<?php

namespace App\Livewire\Dashboard;

use App\Models\ProductionRecord;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

class ActiveRecord extends Component
{
    use Toast;

    public ?ProductionRecord $activeRecord = null;

    // Complete modal
    public bool $showCompleteModal = false;

    public int $processed_quantity = 0;

    public ?string $notes = '';

    public function mount()
    {
        $this->loadActiveRecord();
    }

    #[On('operation-started')]
    public function loadActiveRecord(): void
    {
        $this->activeRecord = auth()->user()->productionRecords()
            ->whereIn('status', ['in_progress', 'paused'])
            ->first();
    }

    public function pauseOperation(): void
    {
        if ($this->activeRecord?->status === 'in_progress') {
            $this->activeRecord->update([
                'status' => 'paused',
                'last_paused_at' => now(),
            ]);
            $this->loadActiveRecord();
            $this->warning('Záznam pozastaven.');
        }
    }

    public function resumeOperation(): void
    {
        if ($this->activeRecord?->status === 'paused') {
            $pauseDuration = now()->diffInSeconds($this->activeRecord->last_paused_at);

            $this->activeRecord->update([
                'status' => 'in_progress',
                'total_paused_seconds' => $this->activeRecord->total_paused_seconds + $pauseDuration,
                'last_paused_at' => null,
            ]);
            $this->loadActiveRecord();
            $this->info('Práce obnovena.');
        }
    }

    public function openCompleteModal(): void
    {
        $this->resetValidation();
        $this->reset(['processed_quantity', 'notes']);
        $this->processed_quantity = 0;
        $this->showCompleteModal = true;
    }

    public function completeOperation(): void
    {
        $this->validate([
            'processed_quantity' => 'required|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        if (! $this->activeRecord) {
            return;
        }

        if ($this->activeRecord->status === 'paused') {
            $pauseDuration = now()->diffInSeconds($this->activeRecord->last_paused_at);
            $this->activeRecord->total_paused_seconds += $pauseDuration;
        }

        $endedAt = now();
        $totalSeconds = $endedAt->diffInSeconds($this->activeRecord->started_at) - $this->activeRecord->total_paused_seconds;
        $workedMinutes = (int) round($totalSeconds / 60);

        $this->activeRecord->update([
            'status' => 'completed',
            'ended_at' => $endedAt,
            'processed_quantity' => $this->processed_quantity,
            'notes' => $this->notes,
            'last_paused_at' => null,
            'worked_minutes' => $workedMinutes,
        ]);

        $this->activeRecord = null;
        $this->showCompleteModal = false;
        $this->dispatch('operation-completed');
        $this->success('Práce úspěšně dokončena.');
    }

    public function render()
    {
        return view('livewire.dashboard.active-record', [
            'klicDokla' => $this->activeRecord?->doklad
                ? trim($this->activeRecord->doklad->KlicDokla)
                : $this->activeRecord?->SysPrimKlicDokladu,
        ]);
    }
}
