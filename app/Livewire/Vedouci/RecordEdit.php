<?php

namespace App\Livewire\Vedouci;

use App\Models\Doklad;
use App\Models\EvPodsestav;
use App\Models\Operace;
use App\Models\PrednOperProstr;
use App\Models\PrednOsobProstr;
use App\Models\ProductionRecord;
use App\Models\Prostredek;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Mary\Traits\Toast;

#[Layout('layouts.app')]
class RecordEdit extends Component
{
    use Toast;

    public User $operator;
    public ?int $editRecordId = null;


    public string $vpSearch = '';
    public ?string $sysPrimKlic = null;
    public ?int $radekEntita = null;
    public ?string $poziceRadku = null;
    public ?int $evPodsestavId = null;
    public ?string $drawingNumber = '';


    public ?string $machineId = '';
    public string $operationId = '';


    public string $startedAt = '';
    public string $endedAt = '';
    public int $quantity = 0;
    public string $notes = '';


    public string $radekFilter = '';
    public string $podsFilter = '';

    public function boot()
    {
        abort_if(! auth()->user()->can('manage production records'), 403);
    }

    public function mount(string $klicSubjektu, ?int $id = null)
    {
        $this->operator = User::where('klic_subjektu', $klicSubjektu)->firstOrFail();

        if ($id) {
            $record = $this->operator->productionRecords()
                ->with('doklad')
                ->findOrFail($id);

            $this->editRecordId = (int) $record->ID;
            $this->sysPrimKlic = trim($record->ZakVP_SysPrimKlic ?? '') ?: null;
            $this->radekEntita = $record->ZakVP_radek_entita;
            $this->poziceRadku = trim($record->ZakVP_pozice_radku ?? '') ?: null;
            $this->evPodsestavId = $record->ev_podsestav_id;
            $this->drawingNumber = trim($record->drawing_number ?? '');
            $this->machineId = trim($record->machine_id ?? '');
            $this->operationId = trim($record->operation_id ?? '');
            $this->startedAt = $record->started_at?->format('Y-m-d\TH:i') ?? '';
            $this->endedAt = $record->ended_at?->format('Y-m-d\TH:i') ?? '';
            $this->quantity = (int) ($record->processed_quantity ?? 0);
            $this->notes = $record->notes ?? '';

            $this->validateCascadeIntegrity();
        } else {
            $this->startedAt = now()->format('Y-m-d\TH:i');
            $this->endedAt = now()->format('Y-m-d\TH:i');
        }
    }


    private function validateCascadeIntegrity(): void
    {
        if ($this->sysPrimKlic && ! $this->selectedDoklad) {
            $this->sysPrimKlic = null;
            $this->radekEntita = null;
            $this->evPodsestavId = null;
            $this->poziceRadku = null;
            $this->drawingNumber = '';
            return;
        }

        if ($this->radekEntita && $this->selectedDoklad) {
            $radekExists = $this->selectedDoklad->radky->contains('EntitaRad', $this->radekEntita);
            if (! $radekExists) {
                $this->radekEntita = null;
                $this->evPodsestavId = null;
                $this->poziceRadku = null;
            }
        }

        if ($this->evPodsestavId && $this->radekEntita) {
            $podsExists = EvPodsestav::where('ID', $this->evPodsestavId)
                ->where('EntitaRadkuVP', $this->radekEntita)
                ->exists();
            if (! $podsExists) {
                $this->evPodsestavId = null;
            }
        }

        if ($this->machineId) {
            $machineExists = $this->availableMachines->contains('machine_key', $this->machineId);
            if (! $machineExists) {
                $exists = Prostredek::where('KlicProstredku', $this->machineId)->exists();
                if (! $exists) {
                    $this->machineId = '';
                    $this->operationId = '';
                }
            }
        }

        if ($this->operationId && $this->machineId) {
            $opExists = $this->machineOperations->contains('operation_key', $this->operationId);
            if (! $opExists) {
                $exists = Operace::where('KlicPoloz', $this->operationId)->exists();
                if (! $exists) {
                    $this->operationId = '';
                }
            }
        }
    }

    #[Computed]
    public function vpSearchResults()
    {
        if (mb_strlen(trim($this->vpSearch)) < 2) {
            return collect();
        }

        $term = mb_substr(mb_strtoupper(trim($this->vpSearch)), 0, 10);

        return Doklad::allTypes()
            ->searchByTerm($term)
            ->orderByDesc('KlicDokla')
            ->limit(12)
            ->get();
    }

    #[Computed]
    public function selectedDoklad(): ?Doklad
    {
        if (! $this->sysPrimKlic) {
            return null;
        }

        return Doklad::allTypes()
            ->with(['radky.materialPolozka', 'radky.evPodsestavy'])
            ->where('SysPrimKlicDokladu', $this->sysPrimKlic)
            ->first();
    }

    #[Computed]
    public function dokladRadky()
    {
        $doklad = $this->selectedDoklad;
        if (! $doklad) {
            return collect();
        }

        $radky = $doklad->radky;

        if ($this->radekFilter) {
            $filter = mb_strtolower(trim($this->radekFilter));
            $radky = $radky->filter(fn ($r) => str_contains(mb_strtolower(trim($r->CisloRadk ?? '')), $filter)
                || str_contains(mb_strtolower(trim($r->Pozice ?? '')), $filter)
                || str_contains(mb_strtolower(trim($r->materialPolozka?->Nazev1 ?? '')), $filter)
            );
        }

        return $radky->values();
    }

    #[Computed]
    public function radekPodsestavy()
    {
        if (! $this->sysPrimKlic || ! $this->radekEntita) {
            return collect();
        }

        $podsestavy = EvPodsestav::where('EntitaRadkuVP', $this->radekEntita)->get();

        if ($this->podsFilter) {
            $filter = mb_strtolower(trim($this->podsFilter));
            $podsestavy = $podsestavy->filter(fn ($p) => str_contains(mb_strtolower(trim($p->OznaceniPodsestavy ?? '')), $filter)
                || str_contains(mb_strtolower(trim($p->Pozice ?? '')), $filter)
                || str_contains(mb_strtolower(trim($p->CisloVykresu ?? '')), $filter)
            );
        }

        return $podsestavy->values();
    }

    #[Computed]
    public function selectedEvPodsestav(): ?EvPodsestav
    {
        return $this->evPodsestavId ? EvPodsestav::find($this->evPodsestavId) : null;
    }

    #[Computed]
    public function selectedMachine(): ?object
    {
        if (! $this->machineId) {
            return null;
        }

        return $this->availableMachines->firstWhere('machine_key', $this->machineId);
    }

    #[Computed]
    public function selectedOperation(): ?object
    {
        if (! $this->operationId || ! $this->machineId) {
            return null;
        }

        return $this->machineOperations->firstWhere('operation_key', $this->operationId);
    }

    #[Computed]
    public function availableMachines()
    {
        $klicSubjektu = $this->operator->klic_subjektu;

        $assigned = collect();
        if ($klicSubjektu) {
            $assigned = PrednOsobProstr::forOsoba($klicSubjektu)
                ->with('prostredek')
                ->orderBy('Priorita')
                ->get();
        }

        $allAssignedMachineKeys = PrednOsobProstr::pluck('Prrostredek')
            ->map(fn ($k) => trim($k))
            ->unique()
            ->values();

        $unassigned = Prostredek::dbcnt(730550)
            ->where('KlicProstredku', 'like', '20%')
            ->whereNotIn('KlicProstredku', $allAssignedMachineKeys)
            ->orderBy('KlicProstredku')
            ->get();

        $machines = collect();

        foreach ($assigned as $r) {
            $machines->push((object) [
                'machine_key' => trim($r->Prrostredek ?? ''),
                'machine_name' => trim($r->prostredek?->NazevUplny ?? ''),
                'assigned' => true,
            ]);
        }

        foreach ($unassigned as $prostredek) {
            $machines->push((object) [
                'machine_key' => trim($prostredek->KlicProstredku),
                'machine_name' => trim($prostredek->NazevUplny ?? ''),
                'assigned' => false,
            ]);
        }

        return $machines;
    }

    #[Computed]
    public function machineOperations()
    {
        if (! $this->machineId) {
            return collect();
        }

        $assigned = PrednOperProstr::forProstredek($this->machineId)
            ->with('operace')
            ->get()
            ->map(function ($r) {
                $r->operation_key = trim($r->Operace ?? '');
                $r->operation_name = trim($r->operace?->Nazev1 ?? '');

                return $r;
            });

        // Operace bez přiřazeného stroje (Prostredek = '~')
        $unassigned = PrednOperProstr::where('Prostredek', '~')
            ->with('operace')
            ->get()
            ->map(function ($r) {
                $r->operation_key = trim($r->Operace ?? '');
                $r->operation_name = trim($r->operace?->Nazev1 ?? '');

                return $r;
            });

        return $assigned->concat($unassigned);
    }


    public function selectDoklad(string $sysPrimKlic): void
    {
        $this->sysPrimKlic = $sysPrimKlic;
        $this->radekEntita = null;
        $this->evPodsestavId = null;
        $this->drawingNumber = '';
        $this->poziceRadku = null;
        $this->vpSearch = '';
        $this->resetErrorBag();
    }

    public function clearDoklad(): void
    {
        $this->sysPrimKlic = null;
        $this->radekEntita = null;
        $this->evPodsestavId = null;
        $this->drawingNumber = '';
        $this->poziceRadku = null;
        $this->resetErrorBag();
    }

    public function selectRadek(int $entitaRad): void
    {
        $this->radekEntita = $entitaRad;
        $this->evPodsestavId = null;
        $this->drawingNumber = '';

        $radek = $this->dokladRadky->firstWhere('EntitaRad', $entitaRad);
        $this->poziceRadku = $radek ? $radek->Pozice : null;
        $this->resetErrorBag();
    }

    public function clearRadek(): void
    {
        $this->radekEntita = null;
        $this->evPodsestavId = null;
        $this->drawingNumber = '';
        $this->poziceRadku = null;
    }

    public function selectPodsestava(int $id): void
    {
        $this->evPodsestavId = $id;
        $evPods = EvPodsestav::find($id);
        if ($evPods) {
            $this->drawingNumber = trim($evPods->CisloVykresu ?? '');
        }
    }

    public function clearPodsestava(): void
    {
        $this->evPodsestavId = null;
        $this->drawingNumber = '';
    }

    public function selectMachine(string $machineKey): void
    {
        $this->machineId = $machineKey;

        $ops = PrednOperProstr::forProstredek($machineKey)->get();
        if ($ops->isNotEmpty()) {
            $this->operationId = trim($ops->first()->Operace ?? '');
        } else {
            $freeOp = PrednOperProstr::where('Prostredek', '~')->first();
            $this->operationId = $freeOp ? trim($freeOp->Operace ?? '') : '';
        }

        $this->resetErrorBag('machineId');
        $this->resetErrorBag('operationId');
    }

    public function selectOperation(string $operationKey): void
    {
        $this->operationId = $operationKey;
        $this->resetErrorBag('operationId');
    }

    public function saveRecord(): void
    {
        $this->resetErrorBag();

        if (! $this->sysPrimKlic) {
            $this->addError('sysPrimKlic', 'Výrobní příkaz je povinný.');
            return;
        }

        $doklad = $this->selectedDoklad;
        if (! $doklad) {
            $this->addError('sysPrimKlic', 'Vybraný doklad nebyl nalezen.');
            return;
        }

        if ($this->radekEntita) {
            $radekExists = $doklad->radky->contains('EntitaRad', $this->radekEntita);
            if (! $radekExists) {
                $this->addError('radekEntita', 'Vybraný řádek nepatří k tomuto dokladu.');
                return;
            }
        }

        if ($this->evPodsestavId) {
            if (! $this->radekEntita) {
                $this->addError('evPodsestavId', 'Pro výběr podsestavy musíte nejdříve vybrat řádek.');
                return;
            }
            $podsExists = EvPodsestav::where('ID', $this->evPodsestavId)
                ->where('EntitaRadkuVP', $this->radekEntita)
                ->exists();
            if (! $podsExists) {
                $this->addError('evPodsestavId', 'Vybraná podsestava nepatří k tomuto řádku.');
                return;
            }
        }

        if (! $this->machineId) {
            $this->addError('machineId', 'Stroj je povinný.');
            return;
        }

        if (! $this->operationId) {
            $this->addError('operationId', 'Operace je povinná.');
            return;
        }

        if (! $this->startedAt) {
            $this->addError('startedAt', 'Začátek je povinný.');
            return;
        }

        if (! $this->endedAt) {
            $this->addError('endedAt', 'Konec je povinný.');
            return;
        }

        $start = Carbon::parse($this->startedAt);
        $end = Carbon::parse($this->endedAt);

        if ($end->lte($start)) {
            $this->addError('endedAt', 'Konec musí být po začátku.');
            return;
        }

        if ($start->gt(now()->addMinutes(5))) {
            $this->addError('startedAt', 'Začátek nemůže být v budoucnosti.');
            return;
        }

        if ($this->quantity < 0) {
            $this->addError('quantity', 'Množství nesmí být záporné.');
            return;
        }

        $pracovisteId = null;
        if ($this->machineId) {
            $prostredek = Prostredek::where('KlicProstredku', $this->machineId)->first();
            $pracovisteId = $prostredek?->Pracoviste;
        }

        $data = [
            'ZakVP_SysPrimKlic' => $this->sysPrimKlic,
            'ZakVP_radek_entita' => $this->radekEntita,
            'ZakVP_pozice_radku' => $this->poziceRadku,
            'ev_podsestav_id' => $this->evPodsestavId,
            'drawing_number' => $this->drawingNumber ?: null,
            'machine_id' => $this->machineId ?: null,
            'operation_id' => $this->operationId,
            'pracoviste_id' => $pracovisteId,
            'processed_quantity' => $this->quantity,
            'started_at' => $start,
            'ended_at' => $end,
            'notes' => $this->notes ?: null,
            'SYSTIMEST' => now(),
        ];

        if ($this->editRecordId) {
            $record = $this->operator->productionRecords()->findOrFail($this->editRecordId);
            $record->update($data);
            $this->success('Záznam upraven.');
        } else {
            ProductionRecord::create(array_merge($data, [
                'ID' => ProductionRecord::nextId(),
                'user_id' => $this->operator->klic_subjektu,
                'status' => 2,
                'CTSMP' => now(),
            ]));
            $this->success('Záznam přidán.');
        }

        $this->redirect(route('vedouci.show', $this->operator->klic_subjektu), navigate: true);
    }

    public function render()
    {
        return view('livewire.vedouci.record-edit');
    }
}
