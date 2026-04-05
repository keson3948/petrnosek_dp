<?php

namespace App\Livewire\Vedouci;

use App\Models\Doklad;
use App\Models\EvPodsestav;
use App\Models\PrednOperProstr;
use App\Models\PrednOsobProstr;
use App\Models\ProductionRecord;
use App\Models\Prostredek;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

#[Layout('layouts.app')]
class Show extends Component
{
    use Toast, WithPagination;

    public User $operator;

    // --- Filters ---
    public string $dateFrom = '';

    public string $dateTo = '';

    // --- Add Record ---
    public bool $showAddModal = false;

    public string $add_vpSearch = '';

    public ?string $add_sysPrimKlic = null;

    public string $add_vpLabel = '';

    public ?string $add_machine_id = '';

    public string $add_operation_id = '';

    public int $add_quantity = 0;

    public string $add_startedAt = '';

    public int $add_hours = 0;

    public int $add_minutes = 0;

    public string $add_notes = '';

    // --- Edit Record ---
    public bool $showEditModal = false;

    public ?int $editRecordId = null;

    public ?string $edit_sysPrimKlic = null;

    public string $edit_vpLabel = '';

    public string $edit_vpSearch = '';

    public ?string $edit_machine_id = '';

    public string $edit_operation_id = '';

    public int $edit_quantity = 0;

    public string $edit_startedAt = '';

    public int $edit_hours = 0;

    public int $edit_minutes = 0;

    public string $edit_notes = '';

    public function boot()
    {
        abort_if(! auth()->user()->can('manage production records'), 403);
    }

    public function mount(string $klicSubjektu)
    {
        $this->operator = User::where('klic_subjektu', $klicSubjektu)->firstOrFail();
        $this->dateFrom = now()->subDays(7)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    // ==========================================
    // Computed Properties
    // ==========================================

    #[Computed]
    public function records()
    {
        $query = $this->operator->productionRecords()
            ->with(['doklad', 'machine', 'operation'])
            ->where('status', 2)
            ->orderByDesc('ended_at');

        if ($this->dateFrom) {
            $query->where('ended_at', '>=', $this->dateFrom . ' 00:00:00');
        }
        if ($this->dateTo) {
            $query->where('ended_at', '<=', $this->dateTo . ' 23:59:59');
        }

        return $query->get();
    }

    #[Computed]
    public function operatorMachines()
    {
        $klicSubjektu = $this->operator->klic_subjektu;
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

    #[Computed]
    public function addVpSearchResults()
    {
        if (mb_strlen(trim($this->add_vpSearch)) < 2) {
            return collect();
        }

        $term = mb_substr(mb_strtoupper(trim($this->add_vpSearch)), 0, 10);

        return Doklad::allTypes()
            ->searchByTerm($term)
            ->orderByDesc('KlicDokla')
            ->limit(8)
            ->get();
    }

    #[Computed]
    public function editVpSearchResults()
    {
        if (mb_strlen(trim($this->edit_vpSearch)) < 2) {
            return collect();
        }

        $term = mb_substr(mb_strtoupper(trim($this->edit_vpSearch)), 0, 10);

        return Doklad::allTypes()
            ->searchByTerm($term)
            ->orderByDesc('KlicDokla')
            ->limit(8)
            ->get();
    }

    #[Computed]
    public function addMachineOperations()
    {
        if (! $this->add_machine_id) {
            return collect();
        }

        return PrednOperProstr::forProstredek($this->add_machine_id)
            ->with('operace')
            ->get()
            ->map(function ($r) {
                $r->operation_key = trim($r->Operace ?? '');
                $r->operation_name = trim($r->operace?->Nazev1 ?? '');

                return $r;
            });
    }

    #[Computed]
    public function editMachineOperations()
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

    // ==========================================
    // Helper
    // ==========================================

    public function getRecordInfo(ProductionRecord $record): array
    {
        $workedH = null;
        $workedM = null;

        if ($record->started_at && $record->ended_at) {
            $totalMinutes = max(0, intval($record->started_at->diffInMinutes($record->ended_at)) - ($record->total_paused_min ?? 0));
            $workedH = intdiv($totalMinutes, 60);
            $workedM = $totalMinutes % 60;
        }

        return [
            'workedH' => $workedH,
            'workedM' => $workedM,
        ];
    }

    // ==========================================
    // Add Record
    // ==========================================

    public function openAddModal(): void
    {
        $this->reset([
            'add_vpSearch', 'add_sysPrimKlic', 'add_vpLabel',
            'add_machine_id', 'add_operation_id', 'add_quantity',
            'add_startedAt', 'add_hours', 'add_minutes', 'add_notes',
        ]);
        $this->add_startedAt = now()->format('Y-m-d\TH:i');
        $this->resetValidation();

        $firstMachine = $this->operatorMachines->first();
        if ($firstMachine) {
            $this->add_machine_id = $firstMachine->machine_key;
            $ops = $this->addMachineOperations;
            if ($ops->count() >= 1) {
                $this->add_operation_id = $ops->first()->operation_key;
            }
        }

        $this->showAddModal = true;
    }

    public function addSelectVp(string $sysPrimKlic, string $label): void
    {
        $this->add_sysPrimKlic = $sysPrimKlic;
        $this->add_vpLabel = $label;
        $this->add_vpSearch = '';
    }

    public function addClearVp(): void
    {
        $this->add_sysPrimKlic = null;
        $this->add_vpLabel = '';
    }

    public function addSelectMachine(string $machineKey): void
    {
        $this->add_machine_id = $machineKey;
        $ops = PrednOperProstr::forProstredek($machineKey)->get();
        $this->add_operation_id = $ops->count() === 1 ? trim($ops->first()->Operace) : '';
    }

    public function saveAddRecord(): void
    {
        if (! $this->add_operation_id) {
            $this->addError('add_operation_id', 'Operace je povinná.');

            return;
        }

        if ($this->add_hours < 0 || $this->add_minutes < 0) {
            $this->addError('add_time', 'Čas nesmí být záporný.');

            return;
        }

        $workedMinutes = ($this->add_hours * 60) + $this->add_minutes;

        if ($workedMinutes === 0) {
            $this->addError('add_time', 'Odpracovaný čas musí být větší než 0.');

            return;
        }

        if (! $this->add_startedAt) {
            $this->addError('add_startedAt', 'Začátek je povinný.');

            return;
        }

        if ($this->add_quantity < 0) {
            $this->addError('add_quantity', 'Množství nesmí být záporné.');

            return;
        }

        $start = \Carbon\Carbon::parse($this->add_startedAt);
        $endedAt = $start->copy()->addMinutes($workedMinutes);

        $pracovisteId = null;
        if ($this->add_machine_id) {
            $prostredek = Prostredek::where('KlicProstredku', $this->add_machine_id)->first();
            $pracovisteId = $prostredek?->Pracoviste;
        }

        $nextId = ProductionRecord::nextId();

        ProductionRecord::create([
            'ID' => $nextId,
            'machine_id' => $this->add_machine_id ?: null,
            'user_id' => $this->operator->klic_subjektu,
            'started_at' => $start,
            'ended_at' => $endedAt,
            'pracoviste_id' => $pracovisteId,
            'operation_id' => $this->add_operation_id,
            'ZakVP_SysPrimKlic' => $this->add_sysPrimKlic,
            'processed_quantity' => $this->add_quantity,
            'notes' => $this->add_notes ?: null,
            'status' => 2,
            'CTSMP' => now(),
            'SYSTIMEST' => now(),
        ]);

        $this->showAddModal = false;
        $this->success('Záznam přidán.');
    }

    // ==========================================
    // Edit Record
    // ==========================================

    public function openEditModal(int $id): void
    {
        $record = $this->operator->productionRecords()->findOrFail($id);
        $this->editRecordId = (int) $record->ID;

        $this->edit_sysPrimKlic = $record->ZakVP_SysPrimKlic;
        $this->edit_vpLabel = $record->doklad ? trim($record->doklad->KlicDokla) : '';
        $this->edit_vpSearch = '';
        $this->edit_machine_id = $record->machine_id ?? '';
        $this->edit_operation_id = $record->operation_id ?? '';
        $this->edit_quantity = (int) ($record->processed_quantity ?? 0);
        $this->edit_notes = $record->notes ?? '';

        $workedMinutes = 0;
        if ($record->started_at && $record->ended_at) {
            $workedMinutes = max(0, intval($record->started_at->diffInMinutes($record->ended_at)) - ($record->total_paused_min ?? 0));
        }

        $this->edit_startedAt = $record->started_at?->format('Y-m-d\TH:i') ?? '';
        $this->edit_hours = intdiv($workedMinutes, 60);
        $this->edit_minutes = $workedMinutes % 60;

        $this->resetValidation();
        $this->showEditModal = true;
    }

    public function editSelectVp(string $sysPrimKlic, string $label): void
    {
        $this->edit_sysPrimKlic = $sysPrimKlic;
        $this->edit_vpLabel = $label;
        $this->edit_vpSearch = '';
    }

    public function editClearVp(): void
    {
        $this->edit_sysPrimKlic = null;
        $this->edit_vpLabel = '';
    }

    public function editSelectMachine(string $machineKey): void
    {
        $this->edit_machine_id = $machineKey;
        $ops = PrednOperProstr::forProstredek($machineKey)->get();
        $this->edit_operation_id = $ops->count() === 1 ? trim($ops->first()->Operace) : '';
    }

    public function saveEditRecord(): void
    {
        if (! $this->edit_operation_id) {
            $this->addError('edit_operation_id', 'Operace je povinná.');

            return;
        }

        if ($this->edit_hours < 0 || $this->edit_minutes < 0) {
            $this->addError('edit_time', 'Čas nesmí být záporný.');

            return;
        }

        $workedMinutes = ($this->edit_hours * 60) + $this->edit_minutes;

        if ($workedMinutes === 0) {
            $this->addError('edit_time', 'Odpracovaný čas musí být větší než 0.');

            return;
        }

        if (! $this->edit_startedAt) {
            $this->addError('edit_startedAt', 'Začátek je povinný.');

            return;
        }

        if ($this->edit_quantity < 0) {
            $this->addError('edit_quantity', 'Množství nesmí být záporné.');

            return;
        }

        $record = $this->operator->productionRecords()->findOrFail($this->editRecordId);

        $start = \Carbon\Carbon::parse($this->edit_startedAt);
        $totalMinutes = $workedMinutes + ($record->total_paused_min ?? 0);
        $endedAt = $start->copy()->addMinutes($totalMinutes);

        $record->update([
            'ZakVP_SysPrimKlic' => $this->edit_sysPrimKlic,
            'machine_id' => $this->edit_machine_id ?: null,
            'operation_id' => $this->edit_operation_id,
            'processed_quantity' => $this->edit_quantity,
            'started_at' => $start,
            'ended_at' => $endedAt,
            'notes' => $this->edit_notes ?: null,
            'SYSTIMEST' => now(),
        ]);

        $this->showEditModal = false;
        $this->success('Záznam upraven.');
    }

    // ==========================================
    // Delete Record
    // ==========================================

    public function deleteRecord(int $id): void
    {
        $record = $this->operator->productionRecords()->findOrFail($id);
        $record->delete();
        $this->success('Záznam smazán.');
    }

    // ==========================================
    // Render
    // ==========================================

    public function render()
    {
        return view('livewire.vedouci.show');
    }
}
