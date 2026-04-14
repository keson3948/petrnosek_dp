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

        $pracoviste = Pracoviste::query()
            ->when($this->search, function ($q) {
                $term = '%' . $this->search . '%';
                $q->where('NazevUplny', 'like', $term)
                    ->orWhere('KlicPracoviste', 'like', $term);
            })
            ->get();

        return view('livewire.admin.area-index', [
            'areas' => $pracoviste,
            'headers' => $headers,
        ]);
    }
}
