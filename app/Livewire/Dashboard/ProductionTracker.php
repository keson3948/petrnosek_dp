<?php

namespace App\Livewire\Dashboard;

use App\Models\Doklad;
use App\Models\PrednOperProstr;
use App\Models\ProductionRecord;
use App\Models\PrednOsobProstr;
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

    public string $vp_number = '';

    public string $operation_id = '';

    public ?string $machine_id = '';

    public ?string $drawing_number = '';

    // Complete Form
    public int $processed_quantity = 0;

    public ?string $notes = '';

    // --- Individual Edit Modals ---
    public ?int $editRecordId = null;

    // Order number edit
    public bool $showEditOrderModal = false;

    public string $edit_order_number = '';

    public string $orderSearch = '';

    // VP number edit
    public bool $showEditVpModal = false;

    public string $edit_vp_number = '';

    public string $vpSearch = '';

    public bool $showEditMachineOpModal = false;

    public ?string $edit_machine_id = '';

    public string $edit_operation_id = '';

    public bool $showEditTimeModal = false;

    public int $edit_hours = 0;

    public int $edit_minutes = 0;

    public ?string $edit_started_at = null;

    public bool $showOrderListInline = false;

    public string $orderListSearch = '';

    public string $orderListTarget = 'order'; // 'order' or 'vp'

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
        $this->reset(['order_number', 'vp_number', 'operation_id', 'machine_id', 'drawing_number']);
        $this->showStartModal = true;
    }

    public function startOperation()
    {
        $this->validate([
            'order_number' => 'required|string|max:255',
            'vp_number' => 'nullable|string|max:255',
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
            'vp_number' => $this->vp_number,
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
            $this->success('Práce úspěšně dokončena.');
        }
    }

    public function openEditOrder(int $id)
    {
        $record = ProductionRecord::where('user_id', auth()->id())->findOrFail($id);
        $this->editRecordId = $record->id;
        $this->edit_order_number = $record->order_number ?? '';
        $this->orderSearch = '';
        $this->resetValidation();
        $this->showEditOrderModal = true;
    }

    public function saveEditOrder()
    {
        $this->validate(['edit_order_number' => 'required|string|max:255']);
        $record = ProductionRecord::where('user_id', auth()->id())->findOrFail($this->editRecordId);
        $record->update(['order_number' => $this->edit_order_number]);
        $this->showEditOrderModal = false;
        $this->success('Číslo zakázky uloženo.');
    }

    // --- VP Number ---
    public function openEditVp(int $id)
    {
        $record = ProductionRecord::where('user_id', auth()->id())->findOrFail($id);
        $this->editRecordId = $record->id;
        $this->edit_vp_number = $record->vp_number ?? '';
        $this->vpSearch = '';
        $this->resetValidation();
        $this->showEditVpModal = true;
    }

    public function saveEditVp()
    {
        $this->validate(['edit_vp_number' => 'nullable|string|max:255']);
        $record = ProductionRecord::where('user_id', auth()->id())->findOrFail($this->editRecordId);
        $record->update(['vp_number' => $this->edit_vp_number]);
        $this->showEditVpModal = false;
        $this->success('Číslo VP uloženo.');
    }

    // --- Toggle Order List (inline within edit modals) ---
    public function openOrderList(string $target = 'order')
    {
        $this->orderListTarget = $target;
        $this->orderListSearch = '';
        $this->showOrderListInline = true;
    }

    public function selectOrder(string $klicDokla, string $nazev)
    {
        if ($this->orderListTarget === 'order') {
            $this->edit_order_number = $klicDokla;
        } else {
            $this->edit_vp_number = $klicDokla;
        }
        $this->showOrderListInline = false;
    }

    public function closeOrderList()
    {
        $this->showOrderListInline = false;
    }

    public function getOrderListProperty()
    {
        $query = Doklad::dbcnt(10904)
            ->whereHas('staDoklad', function ($q) {
                $q->where('TypPohybu', '=', 'EC_ZAKVYR')
                    ->where('Vyhodnoceni', '=', '1');
            })
            ->orderByDesc('KlicDokla');

        if ($this->orderListSearch) {
            $query->where(function ($q) {
                $q->where('KlicDokla', 'like', '%'.$this->orderListSearch.'%');
            });
        }

        return $query->paginate(15);
    }

    public function openEditMachineOp(int $id)
    {
        $record = ProductionRecord::where('user_id', auth()->id())->findOrFail($id);
        $this->editRecordId = $record->id;
        $this->edit_machine_id = $record->machine_id ?? '';
        $this->edit_operation_id = $record->operation_id ?? '';
        $this->resetValidation();
        $this->showEditMachineOpModal = true;
    }

    public function selectMachine(string $machineKey, string $machineName)
    {
        $this->edit_machine_id = $machineKey;
        $operations = PrednOperProstr::forProstredek($machineKey)->get();
        if ($operations->count() === 1) {
            $this->edit_operation_id = trim($operations->first()->Operace);
        } else {
            $this->edit_operation_id = '';
        }
    }

    public function selectOperation(string $operationKey)
    {
        $this->edit_operation_id = $operationKey;
    }

    public function saveEditMachineOp()
    {
        $this->validate([
            'edit_machine_id' => 'nullable|string|max:255',
            'edit_operation_id' => 'required|string|max:255',
        ]);

        $record = ProductionRecord::where('user_id', auth()->id())->findOrFail($this->editRecordId);
        $record->update([
            'machine_id' => $this->edit_machine_id,
            'operation_id' => $this->edit_operation_id,
        ]);

        $this->showEditMachineOpModal = false;
        $this->success('Stroj a operace uloženy.');
    }

    public function getUserMachinesProperty()
    {
        $klicSubjektu = auth()->user()->klic_subjektu;
        if (!$klicSubjektu) {
            return collect();
        }

        return PrednOsobProstr::forOsoba($klicSubjektu)
            ->with('prostredek')
            ->orderBy('Priorita')
            ->get()
            ->map(function ($r) {
                $r->machine_key = trim($r->Prrostredek ?? '');
                $r->machine_name = trim($r->prostredek?->NazevUplny ?? '');
                return $r;
            });
    }

    public function getMachineOperationsProperty()
    {
        if (! $this->edit_machine_id) {
            return collect();
        }

        return PrednOperProstr::forProstredek($this->edit_machine_id)
            ->with('operace')
            ->get()
            ->map(function ($r) {
                $r->operation_key = trim($r->Operace ?? '');
                $r->operation_name = trim($r->operace?->Nazev1 ?? '');
                return $r;
            });
    }

    public function openEditTime(int $id)
    {
        $record = ProductionRecord::where('user_id', auth()->id())->findOrFail($id);
        $this->editRecordId = $record->id;
        $this->edit_started_at = $record->started_at?->format('Y-m-d\TH:i');

        $workedMinutes = $record->worked_minutes;
        if ($workedMinutes === null && $record->started_at && $record->ended_at) {
            $totalSeconds = $record->ended_at->diffInSeconds($record->started_at) - ($record->total_paused_seconds ?? 0);
            $workedMinutes = (int) round($totalSeconds / 60);
        }

        $this->edit_hours = intdiv($workedMinutes ?? 0, 60);
        $this->edit_minutes = ($workedMinutes ?? 0) % 60;

        $this->resetValidation();
        $this->showEditTimeModal = true;
    }

    public function adjustHours(int $delta)
    {
        $this->edit_hours = max(0, $this->edit_hours + $delta);
    }

    public function adjustMinutes(int $delta)
    {
        $newMinutes = $this->edit_minutes + $delta;
        if ($newMinutes >= 60) {
            $this->edit_hours++;
            $newMinutes -= 60;
        } elseif ($newMinutes < 0) {
            if ($this->edit_hours > 0) {
                $this->edit_hours--;
                $newMinutes += 60;
            } else {
                $newMinutes = 0;
            }
        }
        $this->edit_minutes = $newMinutes;
    }

    public function saveEditTime()
    {
        $record = ProductionRecord::where('user_id', auth()->id())->findOrFail($this->editRecordId);

        $workedMinutes = ($this->edit_hours * 60) + $this->edit_minutes;

        $updateData = [
            'worked_minutes' => $workedMinutes,
            'started_at' => $this->edit_started_at,
        ];

        // Recalculate ended_at based on started_at + worked_minutes + paused_seconds
        if ($this->edit_started_at) {
            $start = \Carbon\Carbon::parse($this->edit_started_at);
            $totalSeconds = ($workedMinutes * 60) + ($record->total_paused_seconds ?? 0);
            $updateData['ended_at'] = $start->addSeconds($totalSeconds);
        }

        $record->update($updateData);

        $this->showEditTimeModal = false;
        $this->success('Čas uložen.');
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

        $planned = collect([]);

        return view('livewire.dashboard.production-tracker', [
            'planned' => $planned,
            'today' => $today,
            'historical' => $historical,
        ]);
    }
}
