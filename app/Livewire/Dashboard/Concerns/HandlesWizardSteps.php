<?php

namespace App\Livewire\Dashboard\Concerns;

use App\Models\EvPodsestav;
use App\Models\Prostredek;
use Livewire\Attributes\On;

trait HandlesWizardSteps
{
    public int $startStep = 1;

    public int $minStep = 1;

    public ?string $selectedSysPrimKlic = null;

    public ?int $selectedDokladRadekEntita = null;

    public ?int $evPodsestavId = null;

    public ?string $drawing_number = '';

    public ?string $machine_id = '';

    public ?string $pracoviste_id = null;

    public ?string $pozice_radku = null;

    public string $operation_id = '';

    public string $podSearch = '';

    public string $radekFilter = '';

    public string $podsFilter = '';

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
            'showTripConfirmation', 'selectedTripKey',
        ]);
        $this->startStep = 1;
        $this->minStep = 1;
        $this->showStartDrawer = true;
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
            5 => $this->advanceFromStep5(),
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
            6 => $this->startStep = 5,
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
            $this->autoSelectMachine();

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
        $this->autoSelectMachine();
    }

    private function advanceFromStep5(): void
    {
        if (! $this->machine_id) {
            $this->addError('machine_id', 'Vyberte stroj.');

            return;
        }
        $this->autoSelectOperation();
        $this->startStep = 6;
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

            return;
        }

        $this->startStep = 4;
    }

    private function isVyrobniPrikaz(): bool
    {
        $doklad = $this->selectedDoklad;

        return $doklad && (int) $doklad->DBCNTID === 10904;
    }

    private function autoSelectMachine(): void
    {
        $firstMachine = $this->userMachines->first();
        if ($firstMachine) {
            $this->machine_id = $firstMachine->machine_key;
            $this->pracoviste_id = $firstMachine->prostredek ? $firstMachine->prostredek->Pracoviste : null;
        }
    }

    private function autoSelectOperation(): void
    {
        $firstOperation = $this->startMachineOperations->first();
        if ($firstOperation) {
            $this->operation_id = $firstOperation->operation_key;
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
        $this->autoSelectMachine();
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
            $this->autoSelectMachine();
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
        $this->autoSelectMachine();
    }

    public function startSelectMachine(string $machineKey): void
    {
        $this->machine_id = $machineKey;
        $this->operation_id = '';
        $m = $this->userMachines->firstWhere('machine_key', $machineKey);
        if ($m) {
            $this->pracoviste_id = $m->prostredek ? $m->prostredek->Pracoviste : null;
        } else {
            $prostredek = Prostredek::where('KlicProstredku', $machineKey)->first();
            $this->pracoviste_id = $prostredek ? $prostredek->Pracoviste : null;
        }
    }

    public function startSelectOperation(string $operationKey): void
    {
        $this->operation_id = $operationKey;
    }
}
