<?php

namespace App\Livewire\Vedouci;

use App\Models\Pracoviste;
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

    public string $activeTab = 'all';

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

    #[Computed]
    public function halls()
    {
        return Pracoviste::all()
            ->map(function ($p) {
                $name = trim($p->NazevUplny ?? '');
                if (preg_match('/^(H\d+)/', $name, $m)) {
                    return $m[1];
                }
                return null;
            })
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        $query = Prostredek::dbcnt(730550)
            ->where('KlicProstredku', 'like', '20%')
            ->with('pracoviste');

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

            $pracovisteName = trim($prostredek->pracoviste?->NazevUplny ?? '');
            $hall = '';
            if (preg_match('/^(H\d+)/', $pracovisteName, $m)) {
                $hall = $m[1];
            }

            return (object) [
                'key' => $key,
                'name' => trim($prostredek->NazevUplny ?? $key),
                'hall' => $hall,
                'pracoviste' => $pracovisteName,
                'is_active' => (bool) $active,
                'status_label' => $active ? ($active->status == 1 ? 'Pauza' : 'V provozu') : 'Volný',
                'active_user' => $active ? ($userNames[trim($active->user_id)] ?? trim($active->user_id)) : '',
                'active_user_klic' => $active ? trim($active->user_id) : '',
                'active_vp' => $active ? trim(($active->doklad?->MPSProjekt ?? '') . ' ' . ($active->doklad?->KlicDokla ?? '')) : '',
                'active_vp_sys_klic' => $active ? trim($active->ZakVP_SysPrimKlic ?? '') : '',
                'active_operation' => $active ? trim($active->operation?->Nazev1 ?? $active->operation_id ?? '') : '',
            ];
        });

        // Filter by hall tab
        if ($this->activeTab !== 'all') {
            $rows = $rows->filter(fn ($r) => $r->hall === $this->activeTab);
        }

        // Sort: active first, then by pracoviste, then name
        $rows = $rows->sortBy([
            ['is_active', 'desc'],
            ['pracoviste', 'asc'],
            ['name', 'asc'],
        ])->values();

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
            'hallTabs' => $this->halls,
        ]);
    }
}
