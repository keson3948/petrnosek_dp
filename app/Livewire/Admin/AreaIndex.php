<?php

namespace App\Livewire\Admin;

use App\Models\Pracoviste;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class AreaIndex extends Component
{
    public string $search = '';

    public function boot()
    {
        abort_if(!auth()->user()->can('manage areas'), 403);
    }

    public function render()
    {
        $headers = [
            ['key' => 'KlicPracoviste', 'label' => 'Klíč'],
            ['key' => 'NazevUplny', 'label' => 'Název'],
            ['key' => 'VedouciOsoba', 'label' => 'Vedoucí'],
        ];

        $pracoviste = Pracoviste::all();

        if ($this->search) {
            $term = mb_strtolower($this->search);
            $pracoviste = $pracoviste->filter(fn ($p) =>
                str_contains(mb_strtolower($p->NazevUplny ?? ''), $term)
                || str_contains(mb_strtolower($p->KlicPracoviste ?? ''), $term)
            )->values();
        }

        return view('livewire.admin.area-index', [
            'areas' => $pracoviste,
            'headers' => $headers,
        ]);
    }
}
