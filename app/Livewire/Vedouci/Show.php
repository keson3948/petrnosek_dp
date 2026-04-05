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

    // --- Modal mode: 'add' or 'edit' ---
    public bool $showModal = false;

    public string $modalMode = 'add'; // 'add' or 'edit'

    public int $modalStep = 1;

    public ?int $editRecordId = null;

    // --- Modal fields (shared for add/edit) ---
    public string $m_vpSearch = '';

    public ?string $m_sysPrimKlic = null;

    public ?int $m_radekEntita = null;

    public ?string $m_poziceRadku = null;

    public ?int $m_evPodsestavId = null;

    public ?string $m_drawing_number = '';

    public ?string $m_machine_id = '';

    public string $m_operation_id = '';

    public string $m_startedAt = '';

    public string $m_endedAt = '';

    public int $m_quantity = 0;

    public string $m_notes = '';

    public string $m_radekFilter = '';

    public string $m_podsFilter = '';

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
    public function activeRecord()
    {
        return ProductionRecord::where('user_id', $this->operator->klic_subjektu)
            ->whereIn('status', [0, 1])
            ->with(['doklad', 'machine', 'operation'])
            ->first();
    }

    public function getTableHeaders(): array
    {
        return [
            ['key' => 'started_at', 'label' => 'Začátek'],
            ['key' => 'ended_at', 'label' => 'Konec'],
            ['key' => 'vp', 'label' => 'VP'],
            ['key' => 'machine', 'label' => 'Stroj'],
            ['key' => 'operation', 'label' => 'Operace'],
            ['key' => 'quantity', 'label' => 'Množství'],
            ['key' => 'time', 'label' => 'Čas'],
            ['key' => 'notes', 'label' => 'Poznámka'],
            ['key' => 'actions', 'label' => '', 'class' => 'text-right'],
        ];
    }

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
    public function mVpSearchResults()
    {
        if (mb_strlen(trim($this->m_vpSearch)) < 2) {
            return collect();
        }

        $term = mb_substr(mb_strtoupper(trim($this->m_vpSearch)), 0, 10);

        return Doklad::allTypes()
            ->searchByTerm($term)
            ->orderByDesc('KlicDokla')
            ->limit(12)
            ->get();
    }

    #[Computed]
    public function mSelectedDoklad(): ?Doklad
    {
        if (! $this->m_sysPrimKlic) {
            return null;
        }

        return Doklad::allTypes()
            ->with(['radky.materialPolozka', 'radky.evPodsestavy'])
            ->where('SysPrimKlicDokladu', $this->m_sysPrimKlic)
            ->first();
    }

    #[Computed]
    public function mDokladRadky()
    {
        $doklad = $this->mSelectedDoklad;
        if (! $doklad) {
            return collect();
        }

        $radky = $doklad->radky;

        if ($this->m_radekFilter) {
            $filter = mb_strtolower(trim($this->m_radekFilter));
            $radky = $radky->filter(fn ($r) => str_contains(mb_strtolower(trim($r->CisloRadk ?? '')), $filter)
                || str_contains(mb_strtolower(trim($r->Pozice ?? '')), $filter)
                || str_contains(mb_strtolower(trim($r->materialPolozka?->Nazev1 ?? '')), $filter)
            );
        }

        return $radky->values();
    }

    #[Computed]
    public function mRadekPodsestavy()
    {
        if (! $this->m_sysPrimKlic || ! $this->m_radekEntita) {
            return collect();
        }

        $podsestavy = EvPodsestav::where('EntitaRadkuVP', $this->m_radekEntita)->get();

        if ($this->m_podsFilter) {
            $filter = mb_strtolower(trim($this->m_podsFilter));
            $podsestavy = $podsestavy->filter(fn ($p) => str_contains(mb_strtolower(trim($p->OznaceniPodsestavy ?? '')), $filter)
                || str_contains(mb_strtolower(trim($p->Pozice ?? '')), $filter)
                || str_contains(mb_strtolower(trim($p->CisloVykresu ?? '')), $filter)
            );
        }

        return $podsestavy->values();
    }

    #[Computed]
    public function mEvPodsestav(): ?EvPodsestav
    {
        return $this->m_evPodsestavId ? EvPodsestav::find($this->m_evPodsestavId) : null;
    }

    #[Computed]
    public function mMachineOperations()
    {
        if (! $this->m_machine_id) {
            return collect();
        }

        return PrednOperProstr::forProstredek($this->m_machine_id)
            ->with('operace')
            ->get()
            ->map(function ($r) {
                $r->operation_key = trim($r->Operace ?? '');
                $r->operation_name = trim($r->operace?->Nazev1 ?? '');

                return $r;
            });
    }

    // ==========================================
    // Open Modals
    // ==========================================

    public function openAddModal(): void
    {
        $this->resetModalFields();
        $this->modalMode = 'add';
        $this->modalStep = 1;
        $this->m_startedAt = now()->format('Y-m-d\TH:i');
        $this->m_endedAt = now()->format('Y-m-d\TH:i');
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $record = $this->operator->productionRecords()
            ->with('doklad')
            ->findOrFail($id);

        $this->resetModalFields();
        $this->modalMode = 'edit';
        $this->editRecordId = (int) $record->ID;

        $this->m_sysPrimKlic = $record->ZakVP_SysPrimKlic;
        $this->m_radekEntita = $record->ZakVP_radek_entita;
        $this->m_poziceRadku = $record->ZakVP_pozice_radku;
        $this->m_evPodsestavId = $record->ev_podsestav_id;
        $this->m_drawing_number = $record->drawing_number ?? '';
        $this->m_machine_id = $record->machine_id ?? '';
        $this->m_operation_id = $record->operation_id ?? '';
        $this->m_startedAt = $record->started_at?->format('Y-m-d\TH:i') ?? '';
        $this->m_endedAt = $record->ended_at?->format('Y-m-d\TH:i') ?? '';
        $this->m_quantity = (int) ($record->processed_quantity ?? 0);
        $this->m_notes = $record->notes ?? '';

        // Jump to last step for editing
        $this->modalStep = 5;
        $this->showModal = true;
    }

    private function resetModalFields(): void
    {
        $this->reset([
            'editRecordId',
            'm_vpSearch', 'm_sysPrimKlic', 'm_radekEntita', 'm_poziceRadku',
            'm_evPodsestavId', 'm_drawing_number',
            'm_machine_id', 'm_operation_id',
            'm_startedAt', 'm_endedAt', 'm_quantity', 'm_notes',
            'm_radekFilter', 'm_podsFilter',
        ]);
        $this->resetValidation();
    }

    // ==========================================
    // Step Navigation
    // ==========================================

    public function mNextStep(): void
    {
        match ($this->modalStep) {
            1 => $this->mAdvanceFromStep1(),
            2 => $this->mAdvanceFromStep2(),
            3 => $this->mAdvanceFromStep3(),
            4 => $this->mAdvanceFromStep4(),
            default => null,
        };
    }

    public function mPrevStep(): void
    {
        match ($this->modalStep) {
            2 => $this->modalStep = 1,
            3 => $this->modalStep = 2,
            4 => $this->mGoBackFromStep4(),
            5 => $this->mGoBackFromStep5(),
            default => null,
        };
    }

    private function mAdvanceFromStep1(): void
    {
        if (! $this->m_sysPrimKlic) {
            $this->addError('m_vpSearch', 'Vyberte výrobní příkaz.');

            return;
        }
        $this->modalStep = 2;
    }

    private function mAdvanceFromStep2(): void
    {
        if (! $this->m_radekEntita) {
            return;
        }

        if (! $this->mIsVyrobniPrikaz()) {
            $this->modalStep = 5;
            $this->mAutoSelectMachineAndOperation();

            return;
        }

        $podsCount = $this->mRadekPodsestavy->count();
        $this->modalStep = $podsCount > 0 ? 3 : 4;
    }

    private function mAdvanceFromStep3(): void
    {
        $this->modalStep = $this->m_evPodsestavId ? 5 : 4;
    }

    private function mAdvanceFromStep4(): void
    {
        $this->modalStep = 5;
        $this->mAutoSelectMachineAndOperation();
    }

    private function mGoBackFromStep4(): void
    {
        if ($this->m_radekEntita) {
            $podsCount = $this->mRadekPodsestavy->count();
            $this->modalStep = $podsCount > 0 ? 3 : 2;
        } else {
            $this->modalStep = 2;
        }
    }

    private function mGoBackFromStep5(): void
    {
        if (! $this->mIsVyrobniPrikaz()) {
            $this->modalStep = 2;

            return;
        }

        if ($this->m_evPodsestavId) {
            $this->modalStep = 3;
        } elseif ($this->m_radekEntita) {
            $podsCount = $this->mRadekPodsestavy->count();
            $this->modalStep = $podsCount > 0 ? 3 : 4;
        } else {
            $this->modalStep = 4;
        }
    }

    private function mIsVyrobniPrikaz(): bool
    {
        $doklad = $this->mSelectedDoklad;

        return $doklad && (int) $doklad->DBCNTID === 10904;
    }

    private function mAutoSelectMachineAndOperation(): void
    {
        if ($this->m_machine_id) {
            return;
        }

        $firstMachine = $this->operatorMachines->first();
        if ($firstMachine) {
            $this->m_machine_id = $firstMachine->machine_key;
            $firstOp = $this->mMachineOperations->first();
            if ($firstOp) {
                $this->m_operation_id = $firstOp->operation_key;
            }
        }
    }

    // ==========================================
    // Selection Actions
    // ==========================================

    public function mSelectDoklad(string $sysPrimKlic): void
    {
        $this->m_sysPrimKlic = $sysPrimKlic;
        $this->m_radekEntita = null;
        $this->m_evPodsestavId = null;
        $this->m_drawing_number = '';
        $this->m_vpSearch = '';
        $this->m_poziceRadku = null;

        $this->mNextStep();
    }

    public function mClearDoklad(): void
    {
        $this->m_sysPrimKlic = null;
        $this->m_radekEntita = null;
        $this->m_evPodsestavId = null;
        $this->m_drawing_number = '';
        $this->m_poziceRadku = null;
    }

    public function mSelectRadek(int $entitaRad): void
    {
        $this->m_radekEntita = $entitaRad;
        $this->m_evPodsestavId = null;
        $this->m_drawing_number = '';

        $radek = $this->mDokladRadky->firstWhere('EntitaRad', $entitaRad);
        $this->m_poziceRadku = $radek ? $radek->Pozice : null;

        $this->mNextStep();
    }

    public function mSkipRadek(): void
    {
        $this->m_radekEntita = null;
        $this->m_evPodsestavId = null;
        $this->m_drawing_number = '';

        if (! $this->mIsVyrobniPrikaz()) {
            $this->modalStep = 5;
            $this->mAutoSelectMachineAndOperation();
        } else {
            $this->modalStep = 4;
        }
    }

    public function mSelectPodsestava(int $id): void
    {
        $this->m_evPodsestavId = $id;
        $evPods = EvPodsestav::find($id);
        if ($evPods) {
            $this->m_drawing_number = trim($evPods->CisloVykresu ?? '');
        }

        $this->modalStep = 5;
        $this->mAutoSelectMachineAndOperation();
    }

    public function mSkipPodsestava(): void
    {
        $this->m_evPodsestavId = null;
        $this->m_drawing_number = '';
        $this->modalStep = 4;
    }

    public function mSkipDrawingNumber(): void
    {
        $this->m_drawing_number = '';
        $this->modalStep = 5;
        $this->mAutoSelectMachineAndOperation();
    }

    public function mSelectMachine(string $machineKey): void
    {
        $this->m_machine_id = $machineKey;
        $ops = PrednOperProstr::forProstredek($machineKey)->get();
        $this->m_operation_id = $ops->count() >= 1 ? trim($ops->first()->Operace ?? '') : '';
    }

    public function mSelectOperation(string $operationKey): void
    {
        $this->m_operation_id = $operationKey;
    }

    // ==========================================
    // Save
    // ==========================================

    public function saveRecord(): void
    {
        if (! $this->m_operation_id) {
            $this->addError('m_operation_id', 'Operace je povinná.');

            return;
        }

        if (! $this->m_startedAt) {
            $this->addError('m_startedAt', 'Začátek je povinný.');

            return;
        }

        if (! $this->m_endedAt) {
            $this->addError('m_endedAt', 'Konec je povinný.');

            return;
        }

        $start = \Carbon\Carbon::parse($this->m_startedAt);
        $end = \Carbon\Carbon::parse($this->m_endedAt);

        if ($end->lte($start)) {
            $this->addError('m_endedAt', 'Konec musí být po začátku.');

            return;
        }

        if ($this->m_quantity < 0) {
            $this->addError('m_quantity', 'Množství nesmí být záporné.');

            return;
        }

        $pracovisteId = null;
        if ($this->m_machine_id) {
            $prostredek = Prostredek::where('KlicProstredku', $this->m_machine_id)->first();
            $pracovisteId = $prostredek?->Pracoviste;
        }

        if ($this->modalMode === 'edit' && $this->editRecordId) {
            $record = $this->operator->productionRecords()->findOrFail($this->editRecordId);
            $record->update([
                'ZakVP_SysPrimKlic' => $this->m_sysPrimKlic,
                'ZakVP_radek_entita' => $this->m_radekEntita,
                'ZakVP_pozice_radku' => $this->m_poziceRadku,
                'ev_podsestav_id' => $this->m_evPodsestavId,
                'drawing_number' => $this->m_drawing_number ?: null,
                'machine_id' => $this->m_machine_id ?: null,
                'operation_id' => $this->m_operation_id,
                'pracoviste_id' => $pracovisteId,
                'processed_quantity' => $this->m_quantity,
                'started_at' => $start,
                'ended_at' => $end,
                'notes' => $this->m_notes ?: null,
                'SYSTIMEST' => now(),
            ]);
            $this->showModal = false;
            $this->success('Záznam upraven.');
        } else {
            $nextId = ProductionRecord::nextId();
            ProductionRecord::create([
                'ID' => $nextId,
                'machine_id' => $this->m_machine_id ?: null,
                'user_id' => $this->operator->klic_subjektu,
                'started_at' => $start,
                'ended_at' => $end,
                'pracoviste_id' => $pracovisteId,
                'operation_id' => $this->m_operation_id,
                'ZakVP_SysPrimKlic' => $this->m_sysPrimKlic,
                'ZakVP_radek_entita' => $this->m_radekEntita,
                'ZakVP_pozice_radku' => $this->m_poziceRadku,
                'ev_podsestav_id' => $this->m_evPodsestavId,
                'drawing_number' => $this->m_drawing_number ?: null,
                'processed_quantity' => $this->m_quantity,
                'notes' => $this->m_notes ?: null,
                'status' => 2,
                'CTSMP' => now(),
                'SYSTIMEST' => now(),
            ]);
            $this->showModal = false;
            $this->success('Záznam přidán.');
        }
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
        return view('livewire.vedouci.show', [
            'headers' => $this->getTableHeaders(),
        ]);
    }
}
