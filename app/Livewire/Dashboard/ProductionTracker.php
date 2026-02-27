<?php

namespace App\Livewire\Dashboard;

use App\Models\ProductionRecord;
use Livewire\Component;
use Mary\Traits\Toast;
use Carbon\Carbon;

class ProductionTracker extends Component
{
    use Toast;

    public ?ProductionRecord $activeRecord = null;

    // Modals
    public bool $showStartModal = false;
    public bool $showCompleteModal = false;

    // Start Form
    public string $order_number = '';
    public string $operation_id = '';
    public ?string $machine_id = '';
    public ?string $drawing_number = '';

    // Complete Form
    public int $processed_quantity = 0;
    public ?string $notes = '';

    public function mount()
    {
        $this->loadActiveRecord();
    }

    public function loadActiveRecord()
    {
        $this->activeRecord = ProductionRecord::where('user_id', auth()->id())
            ->whereIn('status', ['in_progress', 'paused'])
            ->first();
    }

    public function openStartModal()
    {
        $this->resetValidation();
        $this->reset(['order_number', 'operation_id', 'machine_id', 'drawing_number']);
        $this->showStartModal = true;
    }

    public function startOperation()
    {
        $this->validate([
            'order_number' => 'required|string|max:255',
            'operation_id' => 'required|string|max:255',
            'machine_id' => 'nullable|string|max:255',
            'drawing_number' => 'nullable|string|max:255',
        ]);

        if ($this->activeRecord) {
            $this->error('Již máte aktivní nebo pozastavený záznam.');
            return;
        }

        ProductionRecord::create([
            'user_id' => auth()->id(),
            'order_number' => $this->order_number,
            'operation_id' => $this->operation_id,
            'machine_id' => $this->machine_id,
            'drawing_number' => $this->drawing_number,
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        $this->showStartModal = false;
        $this->loadActiveRecord();
        $this->success('Výrobní operace započata.');
    }

    public function pauseOperation()
    {
        if ($this->activeRecord && $this->activeRecord->status === 'in_progress') {
            $this->activeRecord->update([
                'status' => 'paused',
                'last_paused_at' => now(),
            ]);
            $this->loadActiveRecord();
            $this->warning('Záznam pozastaven.');
        }
    }

    public function resumeOperation()
    {
        if ($this->activeRecord && $this->activeRecord->status === 'paused') {
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

    public function openCompleteModal()
    {
        $this->resetValidation();
        $this->reset(['processed_quantity', 'notes']);
        // Předvyplnit nulu
        $this->processed_quantity = 0; 
        $this->showCompleteModal = true;
    }

    public function completeOperation()
    {
        $this->validate([
            'processed_quantity' => 'required|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($this->activeRecord) {
            if ($this->activeRecord->status === 'paused') {
                 $pauseDuration = now()->diffInSeconds($this->activeRecord->last_paused_at);
                 $this->activeRecord->total_paused_seconds += $pauseDuration;
            }

            $this->activeRecord->update([
                'status' => 'completed',
                'ended_at' => now(),
                'processed_quantity' => $this->processed_quantity,
                'notes' => $this->notes,
                'last_paused_at' => null,
            ]);

            $this->activeRecord = null;
            $this->showCompleteModal = false;
            $this->success('Práce úspěšně dokončena.');
        }
    }

    public function render()
    {
        return view('livewire.dashboard.production-tracker');
    }
}
