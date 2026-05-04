<?php

namespace App\Console\Commands;

use App\Models\ProductionRecord;
use App\Models\SkuZam;
use App\Models\User;
use App\Models\VztahSubj;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoStartLunch extends Command
{
    protected $signature = 'lunch:auto-start {--force : Skip time window check, process all groups immediately}';

    protected $description = 'Automatically starts lunch for users who missed their scheduled lunch time.';

    private const GRACE_MINUTES = 10;

    private const WINDOW_MINUTES = 5;

    public function handle(): int
    {
        $groups = SkuZam::all()->filter(
            fn ($g) => $g->StanovenyCasProObed !== null && $g->StanovenyCasProObed !== ''
        );

        foreach ($groups as $group) {
            $lunchTime = $group->lunchCarbon();
            $groupKey = trim($group->KlicSkupinyZamestnancu);

            if (! $lunchTime) {
                continue;
            }

            $triggerTime = $lunchTime->copy()->addMinutes(self::GRACE_MINUTES);
            $windowEnd = $triggerTime->copy()->addMinutes(self::WINDOW_MINUTES);

            if (! $this->option('force') && (now()->lt($triggerTime) || now()->gt($windowEnd))) {
                continue;
            }

            // VztahSubj je na Firebirdu — nejdřív získáme klic_subjektu, pak hledáme v MySQL
            $userKeys = VztahSubj::active()
                ->where('SkupinaZamestnancu', $groupKey)
                ->pluck('Subjekt')
                ->map(fn ($k) => trim($k))
                ->filter()
                ->unique()
                ->values();

            $users = User::where('is_active', true)
                ->whereIn('klic_subjektu', $userKeys)
                ->with(['productionRecords' => fn ($q) => $q->work()->where('status', 0)])
                ->get();

            foreach ($users as $user) {
                if ($user->hasLunchToday()) {
                    continue;
                }

                $activeWork = $user->productionRecords->first();

                if ($activeWork) {
                    $activeWork->update([
                        'status' => 1,
                        'last_paused_at' => $lunchTime,
                        'SYSTIMEST' => now(),
                    ]);
                }

                ProductionRecord::create([
                    'ID' => ProductionRecord::nextId(),
                    'user_id' => $user->klic_subjektu,
                    'started_at' => $lunchTime,
                    'status' => 0,
                    'TypZaznamu' => ProductionRecord::TYPE_LUNCH,
                    'SkupinaZamestnancu' => $groupKey,
                    'CTSMP' => now(),
                    'SYSTIMEST' => now(),
                ]);

                Log::info('Auto-lunch started', [
                    'user' => $user->klic_subjektu,
                    'group' => $groupKey,
                    'lunch_time' => $lunchTime->toTimeString(),
                    'had_active_work' => (bool) $activeWork,
                ]);
            }
        }

        return self::SUCCESS;
    }
}
