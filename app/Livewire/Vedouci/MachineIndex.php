<?php

namespace App\Livewire\Vedouci;

use App\Models\ProductionRecord;
use App\Models\Prostredek;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class MachineIndex extends Component
{
    public string $search = '';

    public function boot()
    {
        abort_if(! auth()->user()->can('manage production records'), 403);
    }

    #[Computed]
    public function activeRecordsByMachine()
    {
        return ProductionRecord::whereIn('status', [0, 1])
            ->with(['doklad', 'operation'])
            ->get()
            ->filter(fn ($r) => $r->machine_id)
            ->keyBy(fn ($r) => trim($r->machine_id));
    }

    public function render()
    {
        $query = Prostredek::dbcnt(730550)
            ->where('KlicProstredku', 'like', '20%');

        if ($this->search) {
            $term = $this->search;
            $query->where(function ($q) use ($term) {
                $q->where('KlicProstredku', 'like', "%{$term}%")
                    ->orWhere('NazevUplny', 'like', "%{$term}%");
            });
        }

        $machines = $query->orderBy('KlicProstredku')->get();

        $activeByMachine = $this->activeRecordsByMachine;

        $userNames = User::whereNotNull('klic_subjektu')
            ->pluck('name', 'klic_subjektu')
            ->mapWithKeys(fn ($name, $k) => [trim($k) => $name])
            ->all();

        $rows = $machines->map(function ($prostredek) use ($activeByMachine, $userNames) {
            $key = trim($prostredek->KlicProstredku);
            $active = $activeByMachine->get($key);

            return (object) [
                'key' => $key,
                'name' => trim($prostredek->NazevUplny ?? $key),
                'is_active' => (bool) $active,
                'status_label' => $active ? ($active->status == 1 ? 'Pauza' : 'V provozu') : 'Volný',
                'active_user' => $active ? ($userNames[trim($active->user_id)] ?? trim($active->user_id)) : '',
                'active_vp' => $active ? trim($active->doklad?->KlicDokla ?? '') : '',
                'active_operation' => $active ? trim($active->operation?->Nazev1 ?? $active->operation_id ?? '') : '',
            ];
        });

        $headers = [
            ['key' => 'name', 'label' => 'Stroj'],
            ['key' => 'status_label', 'label' => 'Stav'],
            ['key' => 'active_user', 'label' => 'Operátor'],
            ['key' => 'active_vp', 'label' => 'VP'],
            ['key' => 'active_operation', 'label' => 'Operace'],
        ];

        return view('livewire.vedouci.machine-index', [
            'rows' => $rows,
            'headers' => $headers,
        ]);
    }
}
