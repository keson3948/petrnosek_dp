<?php

namespace App\Livewire\Admin;

use App\Models\Prostredek;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

#[Layout('layouts.app')]
class MachineIndex extends Component
{
    use Toast, WithPagination;

    public string $search = '';

    public function boot()
    {
        abort_if(! auth()->user()->can('manage areas'), 403);
    }

    public function render()
    {
        $headers = [
            ['key' => 'kod', 'label' => 'Kód', 'class' => 'w-32'],
            ['key' => 'nazev', 'label' => 'Název'],
            ['key' => 'pracoviste_nazev', 'label' => 'Pracoviště'],
        ];

        $query = Prostredek::dbcnt(730550)
            ->where('KlicProstredku', 'like', '20%')
            ->with('pracoviste');

        if ($this->search) {
            $term = mb_substr(trim($this->search), 0, 15);
            $query->where(function ($q) use ($term) {
                $q->whereRaw('CAST("KlicProstredku" AS VARCHAR(100)) LIKE ?', ["%{$term}%"])
                    ->orWhereRaw('CAST("NazevUplny" AS VARCHAR(100)) LIKE ?', ["%{$term}%"]);
            });
        }

        $machines = $query->orderBy('KlicProstredku')->paginate(15);

        $machines->getCollection()->transform(function ($p) {
            $p->kod = trim($p->KlicProstredku);
            $p->nazev = trim($p->NazevUplny ?? '');
            $p->pracoviste_nazev = trim($p->pracoviste?->NazevUplny ?? '');
            return $p;
        });

        return view('livewire.admin.machine-index', [
            'machines' => $machines,
            'headers' => $headers,
        ]);
    }
}
