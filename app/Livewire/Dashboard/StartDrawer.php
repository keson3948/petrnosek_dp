<?php

namespace App\Livewire\Dashboard;

use App\Models\Doklad;
use App\Models\EvPodsestav;
use App\Models\PrednOperProstr;
use App\Models\PrednOsobProstr;
use App\Models\ProductionRecord;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

class StartDrawer extends Component
{
    use Toast;

    public bool $showStartDrawer = false;

    public int $startStep = 1;

    // Selections
    public ?string $selectedDokladKey = null;

    public ?int $selectedDokladRadekEntita = null;

    public ?int $evPodsestavId = null;

    // Form fields
    public ?string $drawing_number = '';

    public ?string $machine_id = '';

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
            $this->selectedDokladKey = $vp;
        }

        $this->startStep = 5;
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

        $doklad = Doklad::where('SysPrimKlicDokladu', $sysPrimKlic)->first();
        if (! $doklad) {
            $this->error('Výrobní příkaz nebyl nalezen.');

            return;
        }

        $this->selectedDokladKey = trim($doklad->KlicDokla);

        if ($radekEntita) {
            $this->selectedDokladRadekEntita = $radekEntita;
            $podsCount = EvPodsestav::where('EntitaRadkuVP', $radekEntita)->count();
            $this->startStep = $podsCount > 0 ? 3 : 4;
        } else {
            $this->startStep = 2;
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
            'evPodsestavId', 'podSearch', 'selectedDokladKey',
            'selectedDokladRadekEntita', 'podsFilter', 'radekFilter',
        ]);
        $this->startStep = 1;
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
            'drawing_number' => $this->evPodsestavId ? 'nullable|string|max:255' : 'required|string|max:255',
        ]);

        $hasActive = auth()->user()->productionRecords()
            ->whereIn('status', ['in_progress', 'paused'])
            ->exists();

        if ($hasActive) {
            $this->error('Již máte aktivní nebo pozastavený záznam.');

            return;
        }

        $sysPrimKlic = null;
        if ($this->selectedDokladKey) {
            $sysPrimKlic = Doklad::where('SysPrimKlicDokladu', $this->selectedDokladKey)
                ->value('SysPrimKlicDokladu');
        }

        $drawingNumber = $this->drawing_number;
        if ($this->evPodsestavId) {
            $drawingNumber = trim(EvPodsestav::find($this->evPodsestavId)?->CisloVykresu ?? '');
        }

        ProductionRecord::create([
            'user_id' => auth()->id(),
            'SysPrimKlicDokladu' => $sysPrimKlic,
            'operation_id' => $this->operation_id,
            'machine_id' => $this->machine_id,
            'drawing_number' => $drawingNumber,
            'ev_podsestav_id' => $this->evPodsestavId,
            'doklad_radek_entita' => $this->selectedDokladRadekEntita,
            'status' => 'in_progress',
            'started_at' => now(),
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
        match ($this->startStep) {
            2 => $this->startStep = 1,
            3 => $this->startStep = 2,
            4 => $this->goBackFromStep4(),
            5 => $this->goBackFromStep5(),
            default => null,
        };
    }

    private function advanceFromStep1(): void
    {
        if (! $this->selectedDokladKey) {
            $this->addError('podSearch', 'Vyberte výrobní příkaz.');

            return;
        }
        $this->startStep = 2;
    }

    private function advanceFromStep2(): void
    {
        if (! $this->selectedDokladRadekEntita) {
            $this->addError('radekFilter', 'Vyberte řádek VP.');

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
        if (! $this->drawing_number) {
            $this->addError('drawing_number', 'Zadejte číslo výkresu.');

            return;
        }
        $this->startStep = 5;

        $this->autoSelectMachineAndOperation();
    }

    private function goBackFromStep4(): void
    {
        $podsCount = $this->selectedRadekPodsestavy->count();
        $this->startStep = $podsCount > 0 ? 3 : 2;
    }

    private function goBackFromStep5(): void
    {
        $this->startStep = $this->evPodsestavId ? 3 : 4;
    }

    private function autoSelectMachineAndOperation(): void
    {
        $firstMachine = $this->userMachines->first();
        if ($firstMachine) {
            $this->machine_id = $firstMachine->machine_key;

            $firstOperation = $this->startMachineOperations->first();
            if ($firstOperation) {
                $this->operation_id = $firstOperation->operation_key;
            }
        }
    }

    // ==========================================
    // Selection Actions
    // ==========================================

    public function selectDoklad(string $klicDokla): void
    {
        $this->selectedDokladKey = $klicDokla;
        $this->selectedDokladRadekEntita = null;
        $this->evPodsestavId = null;
        $this->drawing_number = '';
        $this->podSearch = '';

        $this->nextStartStep();
    }

    public function clearDoklad(): void
    {
        $this->selectedDokladKey = null;
        $this->selectedDokladRadekEntita = null;
        $this->evPodsestavId = null;
        $this->drawing_number = '';
    }

    public function selectRadek(int $entitaRad): void
    {
        $this->selectedDokladRadekEntita = $entitaRad;
        $this->evPodsestavId = null;
        $this->drawing_number = '';

        $this->nextStartStep();
    }

    public function clearRadek(): void
    {
        $this->selectedDokladRadekEntita = null;
        $this->evPodsestavId = null;
        $this->drawing_number = '';
    }

    public function selectPodsestava(int $id): void
    {
        $this->evPodsestavId = $id;
        $evPods = EvPodsestav::find($id);
        if ($evPods) {
            $this->drawing_number = trim($evPods->CisloVykresu ?? '');
        }

        $this->startStep = 5;
    }

    public function clearPodsestava(): void
    {
        $this->evPodsestavId = null;
        $this->drawing_number = '';
    }

    public function skipPodsestava(): void
    {
        $this->evPodsestavId = null;
        $this->startStep = 4;
    }

    public function startSelectMachine(string $machineKey): void
    {
        $this->machine_id = $machineKey;
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

        return Doklad::dbcnt(10904)
            ->whereHas('staDoklad', fn ($q) => $q->where('TypPohybu', 'EC_ZAKVYR')->where('Vyhodnoceni', 1))
            ->where(fn ($q) => $q
                ->whereRaw('CAST("KlicDokla" AS VARCHAR(100)) LIKE ?', ["%{$term}%"])
                ->orWhereRaw('CAST("MPSProjekt" AS VARCHAR(100)) LIKE ?', ["%{$term}%"])
                ->orWhereRaw('CAST("SpecifiSy" AS VARCHAR(100)) LIKE ?', ["%{$term}%"])
            )
            ->orderByDesc('KlicDokla')
            ->limit(8)
            ->get();
    }

    #[Computed]
    public function selectedDokladRadky()
    {
        if (! $this->selectedDokladKey) {
            return collect();
        }

        $doklad = Doklad::with(['radky.materialPolozka', 'radky.evPodsestavy'])
            ->find($this->selectedDokladKey);

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
        if (! $this->selectedDokladKey || ! $this->selectedDokladRadekEntita) {
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
