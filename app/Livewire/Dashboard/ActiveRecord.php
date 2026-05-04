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
            ->work()
            ->whereIn('status', [0, 1])
            ->first();
    }

    public function pauseOperation(): void
    {
        if ($this->activeRecord?->status === 0) {
            $this->activeRecord->update([
                'status' => 1,
                'last_paused_at' => now(),
                'SYSTIMEST' => now(),
            ]);
            $this->loadActiveRecord();
            $this->warning('Záznam pozastaven.');
        }
    }

    public function resumeOperation(): void
    {
        if ($this->activeRecord?->status === 1) {
            $lastPausedAt = \Carbon\Carbon::parse($this->activeRecord->last_paused_at);
            $pauseDuration = (int) $lastPausedAt->diffInMinutes(now());

            $this->activeRecord->update([
                'status' => 0,
                'total_paused_min' => $this->activeRecord->total_paused_min + $pauseDuration,
                'last_paused_at' => null,
                'SYSTIMEST' => now(),
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

        if ($this->activeRecord->status === 1) {
            $lastPausedAt = \Carbon\Carbon::parse($this->activeRecord->last_paused_at);
            $pauseDuration = (int) $lastPausedAt->diffInMinutes(now());
            $this->activeRecord->total_paused_min += $pauseDuration;
        }

        $endedAt = now();

        $startedAt = \Carbon\Carbon::parse($this->activeRecord->started_at);
        $totalMinutes = max(0, (int) $startedAt->diffInMinutes($endedAt) - ($this->activeRecord->total_paused_min ?? 0));

        $updateData = [
            'status' => 2,
            'ended_at' => $endedAt,
            'processed_quantity' => $this->processed_quantity,
            'notes' => $this->notes,
            'last_paused_at' => null,
            'SYSTIMEST' => now(),
        ];

        if ((int) ($this->activeRecord->TypZaznamu ?? 0) !== ProductionRecord::TYPE_LUNCH) {
            $updateData['CasNaZakZadany'] = $totalMinutes * 60;
        }

        $this->activeRecord->update($updateData);

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
                : $this->activeRecord?->ZakVP_SysPrimKlic,
        ]);
    }
}
