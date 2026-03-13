<?php

namespace App\Livewire\Dashboard;

use App\Models\ProductionRecord;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class ProductionTracker extends Component
{
    use Toast, WithPagination;

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

    // Edit Modal
    public bool $showEditModal = false;

    public ?int $editRecordId = null;

    // Edit Form
    public string $edit_order_number = '';

    public string $edit_operation_id = '';

    public ?string $edit_machine_id = '';

    public ?string $edit_drawing_number = '';

    public int $edit_processed_quantity = 0;

    public ?string $edit_notes = '';

    public ?string $edit_started_at = null;

    public ?string $edit_ended_at = null;

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

    public function openEditModal(int $id)
    {
        $record = ProductionRecord::where('user_id', auth()->id())->findOrFail($id);

        $this->editRecordId = $record->id;
        $this->edit_order_number = $record->order_number;
        $this->edit_operation_id = $record->operation_id;
        $this->edit_machine_id = $record->machine_id;
        $this->edit_drawing_number = $record->drawing_number;
        $this->edit_processed_quantity = $record->processed_quantity;
        $this->edit_notes = $record->notes;
        $this->edit_started_at = $record->started_at?->format('Y-m-d\TH:i');
        $this->edit_ended_at = $record->ended_at?->format('Y-m-d\TH:i');

        $this->resetValidation();
        $this->showEditModal = true;
    }

    public function updateRecord()
    {
        $this->validate([
            'edit_order_number' => 'required|string|max:255',
            'edit_operation_id' => 'required|string|max:255',
            'edit_machine_id' => 'nullable|string|max:255',
            'edit_drawing_number' => 'nullable|string|max:255',
            'edit_processed_quantity' => 'required|integer|min:0',
            'edit_notes' => 'nullable|string',
            'edit_started_at' => 'required|date',
            'edit_ended_at' => 'nullable|date|after_or_equal:edit_started_at',
        ]);

        $record = ProductionRecord::where('user_id', auth()->id())->findOrFail($this->editRecordId);

        $record->update([
            'order_number' => $this->edit_order_number,
            'operation_id' => $this->edit_operation_id,
            'machine_id' => $this->edit_machine_id,
            'drawing_number' => $this->edit_drawing_number,
            'processed_quantity' => $this->edit_processed_quantity,
            'notes' => $this->edit_notes,
            'started_at' => $this->edit_started_at,
            'ended_at' => $this->edit_ended_at,
        ]);

        $this->showEditModal = false;
        $this->success('Záznam úspěšně upraven.');
    }

    public function render()
    {
        $todayStart = now()->startOfDay();
        $fiveDaysAgo = now()->subDays(5)->startOfDay();

        $allCompleted = auth()->user()->productionRecords()
            ->where('status', 'completed')
            ->where('ended_at', '>=', $fiveDaysAgo)
            ->orderByDesc('ended_at')
            ->get();

        $today = $allCompleted->filter(function ($record) use ($todayStart) {
            return $record->ended_at >= $todayStart;
        });

        $historical = $allCompleted->filter(function ($record) use ($todayStart) {
            return $record->ended_at < $todayStart;
        });

        // Mock planned operations for the UI illustration placeholder
        $planned = collect([]);

        return view('livewire.dashboard.production-tracker', [
            'planned' => $planned,
            'today' => $today,
            'historical' => $historical,
        ]);
    }
}
