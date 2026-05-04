<?php

namespace App\Livewire\Dashboard;

use App\Models\ProductionRecord;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

class ProductionTracker extends Component
{
    use Toast;

    public bool $hasActiveRecord = false;

    public bool $showLunchConfirm = false;

    public bool $showResumeConfirm = false;

    public function mount()
    {
        $this->checkActiveRecord();
    }

    #[On('operation-started')]
    #[On('operation-completed')]
    public function checkActiveRecord(): void
    {
        $this->hasActiveRecord = auth()->user()->productionRecords()
            ->work()
            ->whereIn('status', [0, 1])
            ->exists();
    }

    public function openStartDrawer(): void
    {
        $this->dispatch('open-start-drawer');
    }

    public function confirmStartLunch(): void
    {
        $user = auth()->user();

        if ($user->hasLunchToday()) {
            $this->error('Dnes jste už měl oběd.');

            return;
        }

        if (! $user->canStartLunchNow()) {
            $lunch = $user->lunchTime();
            $this->error($lunch
                ? 'Oběd můžete zahájit pouze v okně ±10 min od '.$lunch->format('H:i').'.'
                : 'Pro vás není stanoven čas obědu.');

            return;
        }

        $this->showLunchConfirm = true;
    }

    public function cancelLunch(): void
    {
        $this->showLunchConfirm = false;
    }

    public function startLunch(): void
    {
        $user = auth()->user();

        if ($user->hasLunchToday()) {
            $this->showLunchConfirm = false;
            $this->error('Dnes jste už měl oběd.');

            return;
        }

        if (! $user->canStartLunchNow()) {
            $this->showLunchConfirm = false;
            $lunch = $user->lunchTime();
            $this->error($lunch
                ? 'Oběd můžete zahájit pouze v okně ±10 min od '.$lunch->format('H:i').'.'
                : 'Pro vás není stanoven čas obědu.');

            return;
        }

        $activeWork = $user->productionRecords()
            ->work()
            ->where('status', 0)
            ->first();

        if ($activeWork) {
            $activeWork->update([
                'status' => 1,
                'last_paused_at' => now(),
                'SYSTIMEST' => now(),
            ]);
        }

        $skupinaKlic = trim($user->employeeGroup()?->KlicSkupinyZamestnancu ?? '') ?: null;

        ProductionRecord::create([
            'ID' => ProductionRecord::nextId(),
            'user_id' => $user->klic_subjektu,
            'started_at' => now(),
            'status' => 0,
            'TypZaznamu' => ProductionRecord::TYPE_LUNCH,
            'SkupinaZamestnancu' => $skupinaKlic,
            'CTSMP' => now(),
            'SYSTIMEST' => now(),
        ]);

        $this->showLunchConfirm = false;
        $this->checkActiveRecord();
        $this->dispatch('operation-started');
        $this->success('Oběd zahájen. Trvá 30 minut.');
    }

    public function confirmResumeAfterLunch(): void
    {
        $this->showResumeConfirm = true;
    }

    public function cancelResume(): void
    {
        $this->showResumeConfirm = false;
    }

    public function resumeAfterLunch(): void
    {
        $user = auth()->user();
        $activeLunch = $user->activeLunchRecord();

        if (! $activeLunch) {
            $this->showResumeConfirm = false;
            $this->error('Žádný aktivní oběd.');

            return;
        }

        $lunchStartedAt = Carbon::parse($activeLunch->started_at);
        $lunchEndsAt = $lunchStartedAt->copy()->addMinutes(ProductionRecord::LUNCH_DURATION_MIN);

        $activeLunch->update([
            'status' => 2,
            'ended_at' => $lunchEndsAt,
            'SYSTIMEST' => now(),
        ]);

        $pausedWork = $user->productionRecords()
            ->work()
            ->where('status', 1)
            ->first();

        if ($pausedWork) {
            $pausedWork->update([
                'status' => 0,
                'total_paused_min' => ($pausedWork->total_paused_min ?? 0) + ProductionRecord::LUNCH_DURATION_MIN,
                'last_paused_at' => null,
                'SYSTIMEST' => now(),
            ]);
        }

        $this->showResumeConfirm = false;
        $this->checkActiveRecord();
        $this->dispatch('operation-started');

        $remaining = max(0, (int) ceil(now()->diffInSeconds($lunchEndsAt, false) / 60));
        if ($remaining > 0) {
            $this->success("Operace se začne počítat za {$remaining} min (po konci oběda).");
        } else {
            $this->success('Operace obnovena.');
        }
    }

    public function render()
    {
        $user = auth()->user();
        $activeLunch = $user->activeLunchRecord();

        $lunchEndsAt = null;
        $canResumeEarly = false;
        $hasPausedWork = false;

        if ($activeLunch) {
            $lunchEndsAt = Carbon::parse($activeLunch->started_at)
                ->addMinutes(ProductionRecord::LUNCH_DURATION_MIN);

            $remainingMin = max(0, (int) ceil(now()->diffInSeconds($lunchEndsAt, false) / 60));
            $hasPausedWork = $user->productionRecords()->work()->where('status', 1)->exists();
            $canResumeEarly = $remainingMin <= 5 && $hasPausedWork;
        }

        $group = $user->employeeGroup();
        $lunchTime = $group?->lunchCarbon();

        return view('livewire.dashboard.production-tracker', [
            'activeLunch' => $activeLunch,
            'lunchEndsAt' => $lunchEndsAt,
            'canResumeEarly' => $canResumeEarly,
            'hasPausedWork' => $hasPausedWork,
            'hasLunchToday' => $user->hasLunchToday(),
            'hasLunchGroup' => $lunchTime !== null,
            'canStartLunchNow' => $user->canStartLunchNow(),
            'lunchTime' => $lunchTime,
            'lunchGroupName' => $group ? trim($group->Nazev ?? '') : null,
        ]);
    }
}
