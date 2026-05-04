<?php

namespace App\Livewire\Dashboard;

use App\Livewire\Dashboard\Concerns\HandlesBusinessTrips;
use App\Livewire\Dashboard\Concerns\HandlesQrInput;
use App\Livewire\Dashboard\Concerns\HandlesWizardSteps;
use App\Livewire\Dashboard\Concerns\ProvidesWizardData;
use App\Models\ProductionRecord;
use App\Models\Prostredek;
use App\Models\Terminal;
use Livewire\Component;
use Mary\Traits\Toast;

class StartDrawer extends Component
{
    use Toast;
    use HandlesBusinessTrips;
    use HandlesQrInput;
    use HandlesWizardSteps;
    use ProvidesWizardData;

    public bool $showStartDrawer = false;

    public function mount(?string $qrStart = null, ?string $qrD = null)
    {
        $this->qrStart = $qrStart;
        $this->qrD = $qrD;

        $this->showStartDrawer = false;
        $this->startStep = 1;
        $this->minStep = 1;

        $this->processQrParams();
    }

    public function startOperation(): void
    {
        $this->validate([
            'operation_id' => 'required|string|max:255',
            'machine_id' => 'nullable|string|max:255',
            'drawing_number' => 'nullable|string|max:255',
        ]);

        $hasActive = auth()->user()->productionRecords()
            ->work()
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

        $skupinaKlic = trim(auth()->user()->employeeGroup()?->KlicSkupinyZamestnancu ?? '') ?: null;

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
            'SkupinaZamestnancu' => $skupinaKlic,
            'CTSMP' => now(),
            'SYSTIMEST' => now(),
        ]);

        $this->showStartDrawer = false;
        $this->dispatch('operation-started');

        if (Terminal::isTerminal()) {
            $this->success(
                'Výrobní operace započata.',
                description: 'Budete odhlášeni za 5 sekund.',
                timeout: 5000,
            );
            $this->js("setTimeout(() => window.location.href = '".route('terminal.logout')."', 5000)");

            return;
        }

        $this->success('Výrobní operace započata.');
    }

    public function render()
    {
        return view('livewire.dashboard.start-drawer');
    }
}
