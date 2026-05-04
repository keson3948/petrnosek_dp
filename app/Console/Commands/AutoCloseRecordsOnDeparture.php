<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Mail\RecordsAutoClosedMail;
use App\Models\Attendance\Pruchod;
use App\Models\ProductionRecord;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AutoCloseRecordsOnDeparture extends Command
{
    protected $signature = 'attendance:auto-close-records {--force : Close records for all departed users regardless of grace period}';

    protected $description = 'Auto-close running production records for employees who have clocked out.';

    public function handle(): int
    {
        $activeRecords = ProductionRecord::work()
            ->whereIn('status', [0, 1])
            ->with('doklad')
            ->get()
            ->groupBy(fn ($r) => trim($r->user_id));

        if ($activeRecords->isEmpty()) {
            return self::SUCCESS;
        }

        $userKeys = $activeRecords->keys()->filter()->values();

        $usersWithRecords = User::where('is_active', true)
            ->whereNotNull('osobni_cislo_dochazky')
            ->whereIn('klic_subjektu', $userKeys)
            ->get();

        foreach ($usersWithRecords as $u) {
            $u->setRelation('productionRecords', $activeRecords[trim($u->klic_subjektu)] ?? collect());
        }

        if ($usersWithRecords->isEmpty()) {
            return self::SUCCESS;
        }

        $oscKeys = $usersWithRecords->pluck('osobni_cislo_dochazky')->filter()->unique()->values();

        $pruchody = Pruchod::vceraADnes()
            ->whereIn('OSC', $oscKeys)
            ->orderBy('DATUM')
            ->orderBy('CAS')
            ->get()
            ->groupBy('OSC');

        foreach ($usersWithRecords as $user) {
            $userPruchody = $pruchody[$user->osobni_cislo_dochazky] ?? collect();

            if ($userPruchody->isEmpty()) {
                continue;
            }

            $lastRecord = $userPruchody->last();
            $isPresent = (int) $lastRecord->DIRECTION === 1;

            if ($isPresent) {
                continue;
            }

            $departureAt = Carbon::createFromDate(1900, 1, 1)
                ->addDays((int) $lastRecord->DATUM)
                ->startOfDay()
                ->addMinutes((int) $lastRecord->CAS);

            if (! $this->option('force') && $departureAt->diffInMinutes(now()) < 10) {
                continue;
            }

            $endedAt = $departureAt->copy()->floorMinutes(15);

            $closed = collect();

            foreach ($user->productionRecords as $record) {
                if ($record->SluzebniCesta) {
                    continue;
                }

                $startedAt = Carbon::parse($record->started_at);

                if ($startedAt->greaterThan($endedAt)) {
                    continue;
                }

                $totalMinutes = max(0, (int) $startedAt->diffInMinutes($endedAt) - ($record->total_paused_min ?? 0));

                $record->update([
                    'status'         => 2,
                    'ended_at'       => $endedAt,
                    'last_paused_at' => null,
                    'CasNaZakZadany' => $totalMinutes * 60,
                    'SYSTIMEST'      => now(),
                ]);

                $closed->push($record);

                Log::info('Auto-closed record on departure', [
                    'user'          => $user->klic_subjektu,
                    'record_id'     => $record->ID,
                    'departure_at'  => $departureAt->format('H:i'),
                    'ended_at'      => $endedAt->format('H:i'),
                ]);
            }

            if ($closed->isNotEmpty() && $user->email) {
                Mail::to($user->email)->send(new RecordsAutoClosedMail($closed));
            }
        }

        return self::SUCCESS;
    }
}
