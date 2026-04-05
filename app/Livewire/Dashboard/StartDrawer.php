<?php

namespace App\Livewire\Dashboard;

use App\Models\Doklad;
use App\Models\EvPodsestav;
use App\Models\PrednOperProstr;
use App\Models\PrednOsobProstr;
use App\Models\ProductionRecord;
use App\Models\Prostredek;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

class StartDrawer extends Component
{
    use Toast;

    public bool $showStartDrawer = false;

    public int $startStep = 1;

    public int $minStep = 1;

    // Selections
    public ?string $selectedSysPrimKlic = null;

    public ?int $selectedDokladRadekEntita = null;

    public ?int $evPodsestavId = null;

    // Form fields
    public ?string $drawing_number = '';

    public ?string $machine_id = '';

    public ?string $pracoviste_id = null;

    public ?string $pozice_radku = null;

    public string $operation_id = '';

    // Filters
    public string $podSearch = '';

    public string $radekFilter = '';

    public string $podsFilter = '';

    // QR params (passed from parent)
    public ?string $qrStart = null;

    public ?string $qrD = null;

    public function mount(?string $qrStart = null, ?string $qrD = null)
    {
        $this->qrStart = $qrStart;
        $this->qrD = $qrD;

        // Reset stavu před QR zpracováním, aby neblikal předchozí krok
        $this->showStartDrawer = false;
        $this->startStep = 1;
        $this->minStep = 1;

        if ($this->qrStart) {
            $this->handleQrPodsestava((int) $this->qrStart);
            $this->cleanUrl();
        } elseif ($this->qrD) {
            $this->handleQrDoklad($this->qrD);
            $this->cleanUrl();
        }
    }

    protected function cleanUrl(): void
    {
        $this->js("history.replaceState({}, '', window.location.pathname)");
    }

    // ==========================================
    // QR Handling
    // ==========================================

    protected function handleQrPodsestava(int $evPodsId): void
    {
        $evPods = EvPodsestav::find($evPodsId);
        if (! $evPods) {
            $this->error('Podsestava nebyla nalezena.');

            return;
        }

        $this->evPodsestavId = $evPods->ID;
        $this->drawing_number = trim($evPods->CisloVykresu ?? '');
        $this->selectedDokladRadekEntita = $evPods->EntitaRadkuVP;

        $vp = trim($evPods->VyrobniPrikaz ?? '');
        if ($vp) {
            $this->selectedSysPrimKlic = $vp;
            if ($this->selectedDokladRadekEntita) {
                $this->pozice_radku = trim($evPods->Pozice ?? '');
            }
        }

        $this->startStep = 5;
        $this->minStep = 5;
        $this->showStartDrawer = true;

        $this->autoSelectMachineAndOperation();

        $this->qrStart = null;
        $this->cleanUrl();
    }

    protected function handleQrDoklad(string $d): void
    {
        $radekEntita = null;

        if (str_contains($d, '.')) {
            [$sysPrimKlic, $radekEntita] = explode('.', $d, 2);
            $radekEntita = (int) $radekEntita;
        } else {
            $sysPrimKlic = $d;
        }

        $this->selectedSysPrimKlic = $sysPrimKlic;

        $doklad = $this->selectedDoklad;
        if (! $doklad) {
            $this->selectedSysPrimKlic = null;
            $this->error('Výrobní příkaz nebyl nalezen.');

            return;
        }

        if ($radekEntita) {
            $this->selectedDokladRadekEntita = $radekEntita;
            $radek = $this->selectedDokladRadky->firstWhere('EntitaRad', $radekEntita);
            $this->pozice_radku = $radek ? $radek->Pozice : null;

            $podsCount = EvPodsestav::where('EntitaRadkuVP', $radekEntita)->count();
            $this->startStep = $podsCount > 0 ? 3 : 4;
            $this->minStep = $this->startStep;
        } else {
            $this->startStep = 2;
            $this->minStep = 2;
        }

        $this->showStartDrawer = true;
    }

    // ==========================================
    // Open / Reset
    // ==========================================

    #[On('open-start-drawer')]
    public function openStartDrawer(): void
    {
        $this->resetValidation();
        $this->reset([
            'operation_id', 'machine_id', 'drawing_number',
            'evPodsestavId', 'podSearch', 'selectedSysPrimKlic',
            'selectedDokladRadekEntita', 'podsFilter', 'radekFilter',
            'pracoviste_id', 'pozice_radku',
        ]);
        $this->startStep = 1;
        $this->minStep = 1;
        $this->showStartDrawer = true;
    }

    // ==========================================
    // Start Operation
    // ==========================================

    public function startOperation(): void
    {
        $this->validate([
            'operation_id' => 'required|string|max:255',
            'machine_id' => 'nullable|string|max:255',
            'drawing_number' => 'nullable|string|max:255',
        ]);

        $hasActive = auth()->user()->productionRecords()
            ->whereIn('status', [0, 1])
            ->exists();

        if ($hasActive) {
            $this->error('Již máte aktivní nebo pozastavený záznam.');

            return;
        }

        $sysPrimKlic = $this->selectedSysPrimKlic;

        $nextId = ProductionRecord::nextId();

        $pracovisteId = $this->pracoviste_id;
        if (! $pracovisteId && $this->machine_id) {
            $prostredek = Prostredek::where('KlicProstredku', $this->machine_id)->first();
            $pracovisteId = $prostredek ? $prostredek->Pracoviste : null;
        }

        ProductionRecord::create([
            'ID' => $nextId,
            'machine_id' => $this->machine_id,
            'user_id' => auth()->user()->klic_subjektu,
            'started_at' => now(),
            'pracoviste_id' => $pracovisteId,
            'operation_id' => $this->operation_id,
            'ZakVP_SysPrimKlic' => $sysPrimKlic,
            'drawing_number' => $this->drawing_number,
            'ev_podsestav_id' => $this->evPodsestavId,
            'ZakVP_radek_entita' => $this->selectedDokladRadekEntita,
            'ZakVP_pozice_radku' => $this->pozice_radku,
            'status' => 0,
            'CTSMP' => now(),
            'SYSTIMEST' => now(),
        ]);

        $this->showStartDrawer = false;
        $this->dispatch('operation-started');
        $this->success('Výrobní operace započata.');
    }

    // ==========================================
    // Step Navigation
    // ==========================================

    public function nextStartStep(): void
    {
        match ($this->startStep) {
            1 => $this->advanceFromStep1(),
            2 => $this->advanceFromStep2(),
            3 => $this->advanceFromStep3(),
            4 => $this->advanceFromStep4(),
            default => null,
        };
    }

    public function prevStartStep(): void
    {
        if ($this->startStep <= $this->minStep) {
            return;
        }

        match ($this->startStep) {
            2 => $this->startStep = 1,
            3 => $this->startStep = 2,
            4 => $this->goBackFromStep4(),
            5 => $this->goBackFromStep5(),
            default => null,
        };

        if ($this->startStep < $this->minStep) {
            $this->startStep = $this->minStep;
        }
    }

    private function advanceFromStep1(): void
    {
        if (! $this->selectedSysPrimKlic) {
            $this->addError('podSearch', 'Vyberte výrobní příkaz.');

            return;
        }
        $this->startStep = 2;
    }

    private function advanceFromStep2(): void
    {
        if (! $this->selectedDokladRadekEntita) {
            $this->addError('radekFilter', 'Vyberte řádek VP nebo použijte tlačítko pro pokračování bez výběru.');

            return;
        }

        if (! $this->isVyrobniPrikaz()) {
            $this->startStep = 5;
            $this->autoSelectMachineAndOperation();

            return;
        }

        $podsCount = $this->selectedRadekPodsestavy->count();
        $this->startStep = $podsCount > 0 ? 3 : 4;
    }

    private function advanceFromStep3(): void
    {
        $this->startStep = $this->evPodsestavId ? 5 : 4;
    }

    private function advanceFromStep4(): void
    {
        $this->startStep = 5;
        $this->autoSelectMachineAndOperation();
    }

    private function goBackFromStep4(): void
    {
        if ($this->selectedDokladRadekEntita) {
            $podsCount = $this->selectedRadekPodsestavy->count();
            $this->startStep = $podsCount > 0 ? 3 : 2;
        } else {
            $this->startStep = 2;
        }
    }

    private function goBackFromStep5(): void
    {
        if (! $this->isVyrobniPrikaz()) {
            $this->startStep = 2;

            return;
        }

        if ($this->evPodsestavId) {
            $this->startStep = 3;
        } elseif ($this->selectedDokladRadekEntita) {
            $podsCount = $this->selectedRadekPodsestavy->count();
            $this->startStep = $podsCount > 0 ? 3 : 4;
        } else {
            $this->startStep = 4;
        }
    }

    private function isVyrobniPrikaz(): bool
    {
        $doklad = $this->selectedDoklad;

        return $doklad && (int) $doklad->DBCNTID === 10904;
    }

    private function autoSelectMachineAndOperation(): void
    {
        $firstMachine = $this->userMachines->first();
        if ($firstMachine) {
            $this->machine_id = $firstMachine->machine_key;
            $this->pracoviste_id = $firstMachine->prostredek ? $firstMachine->prostredek->Pracoviste : null;

            $firstOperation = $this->startMachineOperations->first();
            if ($firstOperation) {
                $this->operation_id = $firstOperation->operation_key;
            }
        }
    }

    // ==========================================
    // Selection Actions
    // ==========================================

    public function selectDoklad(string $sysPrimKlic): void
    {
        $this->selectedSysPrimKlic = $sysPrimKlic;
        $this->selectedDokladRadekEntita = null;
        $this->evPodsestavId = null;
        $this->drawing_number = '';
        $this->podSearch = '';

        $this->nextStartStep();
    }

    public function clearDoklad(): void
    {
        $this->selectedSysPrimKlic = null;
        $this->selectedDokladRadekEntita = null;
        $this->evPodsestavId = null;
        $this->drawing_number = '';
    }

    public function selectRadek(int $entitaRad): void
    {
        $this->selectedDokladRadekEntita = $entitaRad;
        $this->evPodsestavId = null;
        $this->drawing_number = '';

        $radek = $this->selectedDokladRadky->firstWhere('EntitaRad', $entitaRad);
        $this->pozice_radku = $radek ? $radek->Pozice : null;

        $this->nextStartStep();
    }

    public function clearRadek(): void
    {
        $this->selectedDokladRadekEntita = null;
        $this->evPodsestavId = null;
        $this->drawing_number = '';
        $this->pozice_radku = null;
    }

    public function selectPodsestava(int $id): void
    {
        $this->evPodsestavId = $id;
        $evPods = EvPodsestav::find($id);
        if ($evPods) {
            $this->drawing_number = trim($evPods->CisloVykresu ?? '');
        }

        $this->startStep = 5;
        $this->autoSelectMachineAndOperation();
    }

    public function clearPodsestava(): void
    {
        $this->evPodsestavId = null;
        $this->drawing_number = '';
    }

    public function skipRadek(): void
    {
        $this->selectedDokladRadekEntita = null;
        $this->evPodsestavId = null;
        $this->drawing_number = '';

        if (! $this->isVyrobniPrikaz()) {
            $this->startStep = 5;
            $this->autoSelectMachineAndOperation();
        } else {
            $this->startStep = 4;
        }
    }

    public function skipPodsestava(): void
    {
        $this->evPodsestavId = null;
        $this->drawing_number = '';
        $this->startStep = 4;
    }

    public function skipDrawingNumber(): void
    {
        $this->drawing_number = '';
        $this->startStep = 5;
        $this->autoSelectMachineAndOperation();
    }

    public function startSelectMachine(string $machineKey): void
    {
        $this->machine_id = $machineKey;
        $m = $this->userMachines->firstWhere('machine_key', $machineKey);
        if ($m) {
            $this->pracoviste_id = $m->prostredek ? $m->prostredek->Pracoviste : null;
        } else {
            $prostredek = Prostredek::where('KlicProstredku', $machineKey)->first();
            $this->pracoviste_id = $prostredek ? $prostredek->Pracoviste : null;
        }

        $ops = PrednOperProstr::forProstredek($machineKey)->get();
        $this->operation_id = $ops->count() >= 1 ? trim($ops->first()->Operace ?? '') : '';
    }

    public function startSelectOperation(string $operationKey): void
    {
        $this->operation_id = $operationKey;
    }

    // ==========================================
    // Computed Properties
    // ==========================================

    #[Computed]
    public function selectedDoklad(): ?Doklad
    {
        if (! $this->selectedSysPrimKlic) {
            return null;
        }

        return Doklad::allTypes()
            ->with(['radky.materialPolozka', 'radky.evPodsestavy'])
            ->where('SysPrimKlicDokladu', $this->selectedSysPrimKlic)
            ->first();
    }

    #[Computed]
    public function evPodsestav(): ?EvPodsestav
    {
        return $this->evPodsestavId ? EvPodsestav::find($this->evPodsestavId) : null;
    }

    #[Computed]
    public function podSearchResults()
    {
        if (mb_strlen(trim($this->podSearch)) < 2) {
            return collect();
        }

        $term = mb_substr(mb_strtoupper(trim($this->podSearch)), 0, 10);

        return Doklad::allTypes()
            ->searchByTerm($term)
            ->orderByDesc('KlicDokla')
            ->limit(12)
            ->get();
    }

    #[Computed]
    public function selectedDokladRadky()
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
    public function selectedRadekPodsestavy()
    {
        if (! $this->selectedSysPrimKlic || ! $this->selectedDokladRadekEntita) {
            return collect();
        }

        $podsestavy = EvPodsestav::where('EntitaRadkuVP', $this->selectedDokladRadekEntita)->get();

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
    public function startMachineOperations()
    {
        if (! $this->machine_id) {
            return collect();
        }

        return PrednOperProstr::forProstredek($this->machine_id)
            ->with('operace')
            ->get()
            ->map(function ($r) {
                $r->operation_key = trim($r->Operace ?? '');
                $r->operation_name = trim($r->operace?->Nazev1 ?? '');

                return $r;
            });
    }

    #[Computed]
    public function userMachines()
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
        return view('livewire.dashboard.start-drawer');
    }
}
