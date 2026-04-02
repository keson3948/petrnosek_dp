<?php

namespace App\Livewire\Dashboard;

use App\Models\Doklad;
use App\Models\PrednOsobProstr;
use App\Models\PrednOperProstr;
use App\Models\ProductionRecord;
use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

class History extends Component
{
    use Toast;

    // --- Edit Modals ---
    public ?int $editRecordId = null;

    // VP (KlicDokla / SysPrimKlicDokladu) edit
    public bool $showEditVpModal = false;

    public string $edit_klicDokla = '';

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

    #[On('operation-completed')]
    public function refresh(): void
    {
        // Livewire will re-render automatically
    }

    public function getUserMachinesProperty()
    {
        $klicSubjektu = auth()->user()->klic_subjektu;
        if (! $klicSubjektu) {
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

    public function render()
    {
        $todayStart = now()->startOfDay();
        $fiveDaysAgo = now()->subDays(5)->startOfDay();

        // Eager-load doklad, machine a operation. 
        // Modely Prostredek a Polozka interně ořezávají mezery u PK, takže in-memory dictionary poběží bezchybně.
        $allCompleted = auth()->user()->productionRecords()
            ->with(['doklad.vlastniOsoba', 'machine', 'operation'])
            ->where('status', 'completed')
            ->where('ended_at', '>=', $fiveDaysAgo)
            ->orderByDesc('ended_at')
            ->get();

        $today = $allCompleted->filter(fn ($r) => $r->ended_at >= $todayStart);
        $historical = $allCompleted->filter(fn ($r) => $r->ended_at < $todayStart);

        // Resolve mistr (User) from doklad->vlastniOsoba->KlicSubjektu
        $klicSubjektuList = $allCompleted
            ->map(fn ($r) => trim($r->doklad?->vlastniOsoba?->KlicSubjektu ?? ''))
            ->filter()->unique()->values();

        $users = $klicSubjektuList->isNotEmpty()
            ? User::whereIn('klic_subjektu', $klicSubjektuList)->get()->keyBy('klic_subjektu')
            : collect();

        return view('livewire.dashboard.history', [
            'today' => $today,
            'historical' => $historical,
            'mistrUsers' => $users,
        ]);
    }

    /**
     * Helper pro blade – spočítá info o záznamu.
     */
    public function getRecordInfo(ProductionRecord $record, $mistrUsers): array
    {
        $workedH = $record->worked_minutes !== null ? intdiv($record->worked_minutes, 60) : null;
        $workedM = $record->worked_minutes !== null ? $record->worked_minutes % 60 : null;

        $klicSubjektu = trim($record->doklad?->vlastniOsoba?->KlicSubjektu ?? '');
        $mistr = $klicSubjektu ? ($mistrUsers[$klicSubjektu] ?? null) : null;
        $mistrColor = $mistr?->color ?? '#6b7280';
        $mistrCislo = $mistr?->cislo_mistra ?? '??';

        return [
            'workedH' => $workedH,
            'workedM' => $workedM,
            'mistr' => $mistr,
            'mistrColor' => $mistrColor,
            'mistrCislo' => $mistrCislo,
        ];
    }

    public function openEditVp(int $id)
    {
        $record = auth()->user()->productionRecords()->with('doklad')->findOrFail($id);
        $this->editRecordId = $record->id;
        $this->edit_klicDokla = $record->doklad ? trim($record->doklad->KlicDokla) : '';
        $this->vpSearch = '';
        $this->resetValidation();
        $this->showEditVpModal = true;
    }

    public function saveEditVp()
    {
        $this->validate(['edit_klicDokla' => 'required|string|max:255']);
        $record = ProductionRecord::where('user_id', auth()->id())->findOrFail($this->editRecordId);

        // Resolve SysPrimKlicDokladu from KlicDokla
        $sysPrimKlic = Doklad::where('KlicDokla', $this->edit_klicDokla)->value('SysPrimKlicDokladu');
        $record->update(['SysPrimKlicDokladu' => $sysPrimKlic ?? $this->edit_klicDokla]);

        $this->showEditVpModal = false;
        $this->success('VP uložen.');
    }



    public function openOrderList(string $target = 'order')
    {
        $this->orderListTarget = $target;
        $this->orderListSearch = '';
        $this->showOrderListInline = true;
    }

    public function selectOrder(string $klicDokla, string $nazev)
    {
        $this->edit_klicDokla = $klicDokla;
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
        $record = auth()->user()->productionRecords()->findOrFail($id);
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
        $record = auth()->user()->productionRecords()->findOrFail($id);
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

        if ($this->edit_started_at) {
            $start = \Carbon\Carbon::parse($this->edit_started_at);
            $totalSeconds = ($workedMinutes * 60) + ($record->total_paused_seconds ?? 0);
            $updateData['ended_at'] = $start->addSeconds($totalSeconds);
        }

        $record->update($updateData);

        $this->showEditTimeModal = false;
        $this->success('Čas uložen.');
    }
}

