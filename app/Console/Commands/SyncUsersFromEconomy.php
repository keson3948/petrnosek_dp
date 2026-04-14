<?php

namespace App\Console\Commands;

use App\Models\Subjekt;
use App\Models\User;
use App\Models\VztahSubj;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SyncUsersFromEconomy extends Command
{
    protected $signature = 'economy:sync-users';

    protected $description = 'Synchronizuje uživatele z FireBird Economy (eca_VztahSubj + eca_Subjekty) do lokální users tabulky';

    public function handle(): int
    {
        $this->info('Načítám zaměstnance z Economy...');

        try {
            $vztahy = VztahSubj::where('ZpusobPorizeni', 0)
                ->where('NFCRFIDTag', '<>', '')
                ->get();
        } catch (\Exception $e) {
            $this->error('Nepodařilo se připojit k FireBird: '.$e->getMessage());

            return Command::FAILURE;
        }

        $this->info("Nalezeno {$vztahy->count()} zaměstnanců v eca_VztahSubj.");

        // Normalize existing klic_subjektu (trim CHAR padding)
        DB::table('users')
            ->whereNotNull('klic_subjektu')
            ->update(['klic_subjektu' => DB::raw('TRIM(klic_subjektu)')]);

        // Pass 1: Create/update users
        $created = 0;
        $updated = 0;
        $deactivated = 0;
        $skipped = 0;

        foreach ($vztahy as $vztah) {
            $klicSubjektu = trim($vztah->Subjekt ?? '');

            if (empty($klicSubjektu)) {
                $this->warn("Přeskakuji VztahSubj #{$vztah->IDVztahSubj} — prázdný Subjekt.");
                $skipped++;

                continue;
            }

            // Load Subjekt individually to avoid FireBird IN limit
            $subjekt = Subjekt::with('emailKontakt')->where('KlicSubjektu', $klicSubjektu)->first();

            if (! $subjekt) {
                $this->warn("Přeskakuji VztahSubj #{$vztah->IDVztahSubj} — Subjekt '{$klicSubjektu}' nenalezen.");
                $skipped++;

                continue;
            }

            $izo = trim($vztah->NFCRFIDTag ?? '');
            $name = trim($subjekt->Jmeno.' '.$subjekt->Prijmeni);
            $isActive = $vztah->Ukonceno == 0;

            // Check izo uniqueness — skip if another user already has this chip
            $izoForUser = $izo ?: null;
            if ($izoForUser) {
                $existingWithIzo = User::where('izo', $izoForUser)
                    ->where('klic_subjektu', '<>', $klicSubjektu)
                    ->first();

                if ($existingWithIzo) {
                    $this->warn("Duplicitní čip '{$izoForUser}' — již přiřazen uživateli '{$existingWithIzo->name}' (id={$existingWithIzo->id}), přeskakuji pro '{$name}'.");
                    $skipped++;

                    continue;
                }
            }

            $user = User::where('klic_subjektu', $klicSubjektu)->first();

            if ($user) {
                $user->update([
                    'name' => $name,
                    'izo' => $izoForUser ?: $user->izo,
                    'is_active' => $isActive,
                ]);
                $updated++;

                if (! $isActive) {
                    $deactivated++;
                }
            } else {
                $email = optional($subjekt->emailKontakt)->Hodnota;
                if (empty($email)) {
                    $email = ($izo ?: $klicSubjektu).'@rfid.local';
                }

                // Ensure email uniqueness
                $baseEmail = $email;
                $counter = 1;
                while (User::where('email', $email)->exists()) {
                    $email = Str::before($baseEmail, '@')."+{$counter}@".Str::after($baseEmail, '@');
                    $counter++;
                }

                User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make(Str::random(16)),
                    'izo' => $izoForUser,
                    'klic_subjektu' => $klicSubjektu,
                    'is_active' => $isActive,
                ]);
                $created++;

                if (! $isActive) {
                    $deactivated++;
                }
            }
        }

        $this->info("Pass 1 hotov: {$created} vytvořeno, {$updated} aktualizováno, {$deactivated} neaktivních, {$skipped} přeskočeno.");

        // Pass 2: Set manager_id
        $userMap = User::whereNotNull('klic_subjektu')
            ->pluck('id', 'klic_subjektu')
            ->toArray();

        $managersSet = 0;
        $managersNotFound = 0;

        foreach ($vztahy as $vztah) {
            $subjektKlic = trim($vztah->Subjekt ?? '');
            $vedouciKlic = trim($vztah->Vedouci ?? '');

            if (empty($subjektKlic)) {
                continue;
            }

            $userId = $userMap[$subjektKlic] ?? null;

            if (! $userId) {
                continue;
            }

            if (! empty($vedouciKlic)) {
                $managerId = $userMap[$vedouciKlic] ?? null;

                if ($managerId) {
                    User::where('id', $userId)->update(['manager_id' => $managerId]);
                    $managersSet++;
                } else {
                    $this->warn("Vedoucí '{$vedouciKlic}' nenalezen pro uživatele '{$subjektKlic}'.");
                    User::where('id', $userId)->update(['manager_id' => null]);
                    $managersNotFound++;
                }
            } else {
                User::where('id', $userId)->update(['manager_id' => null]);
            }
        }

        $this->info("Pass 2 hotov: {$managersSet} vedoucích nastaveno, {$managersNotFound} vedoucích nenalezeno.");
        $this->info('Synchronizace dokončena.');

        return Command::SUCCESS;
    }
}
