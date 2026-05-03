<?php

namespace App\Livewire\Vedouci;

use App\Models\PrednOsobProstr;
use App\Models\ProductionRecord;
use App\Models\Prostredek;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class MachineShow extends Component
{
    public string $machineKey = '';

    public string $machineName = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public function boot()
    {
        abort_if(! auth()->user()->can('manage production records'), 403);
    }

    public function mount(string $machineKey)
    {
        $prostredek = Prostredek::where('KlicProstredku', $machineKey)->firstOrFail();
        $this->machineKey = trim($prostredek->KlicProstredku);
        $this->machineName = trim($prostredek->NazevUplny ?? $this->machineKey);
        $this->dateFrom = now()->subDays(7)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    #[Computed]
    public function userNames()
    {
        return User::whereNotNull('klic_subjektu')
            ->pluck('name', 'klic_subjektu')
            ->mapWithKeys(fn ($name, $k) => [trim($k) => $name])
            ->all();
    }

    #[Computed]
    public function activeRecord()
    {
        return ProductionRecord::where('machine_id', $this->machineKey)
            ->work()
            ->whereIn('status', [0, 1])
            ->with(['doklad', 'operation'])
            ->first();
    }

    #[Computed]
    public function records()
    {
        $query = ProductionRecord::where('machine_id', $this->machineKey)
            ->where('status', 2)
            ->with(['doklad', 'operation'])
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
    public function assignedUsers()
    {
        $assignments = PrednOsobProstr::where('Prrostredek', $this->machineKey)->get();
        $klice = $assignments->pluck('KlicSubjektu')->map(fn ($k) => trim($k))->filter();

        if ($klice->isEmpty()) {
            return collect();
        }

        return User::whereIn('klic_subjektu', $klice)->get();
    }

    public function getTableHeaders(): array
    {
        return [
            ['key' => 'started_at', 'label' => 'Datum'],
            ['key' => 'operator', 'label' => 'Operátor'],
            ['key' => 'vp', 'label' => 'VP'],
            ['key' => 'operation', 'label' => 'Operace'],
            ['key' => 'quantity', 'label' => 'Množství'],
            ['key' => 'time', 'label' => 'Čas'],
            ['key' => 'notes', 'label' => 'Poznámka'],
        ];
    }

    public function render()
    {
        return view('livewire.vedouci.machine-show', [
            'headers' => $this->getTableHeaders(),
        ]);
    }
}
