<?php

namespace App\Livewire\Polozka;

use App\Models\Polozka;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class Stav extends Component
{

    public Collection $polozky;

    public function mount($polozky)
    {
        $this->polozky = $polozky;
    }

    public function headers(): array
    {
        return [
            ['key' => 'KlicPoloz', 'label' => 'Klíč Položky'],
            ['key' => 'Nazev1', 'label' => 'Název 1'],
            ['key' => 'CisloVykresu', 'label' => 'Číslo Výkresu'],
            ['key' => 'Skupina', 'label' => 'Skupina'],
            ['key' => 'Material', 'label' => 'Materiál'],
            ['key' => 'PovrchovaUprava', 'label' => 'Povrch. úprava'],
            ['key' => 'StavPolozkyNakupuAProdeje', 'label' => 'Stav'],
            ['key' => 'stavDokladu.NazevUplny', 'label' => 'Stav Název'],
            ['key' => 'staPo.Sklad', 'label' => 'Sklad'],
            ['key' => 'bilance', 'label' => 'Bilance'],
        ];
    }
    public function render()
    {
        return view('livewire.polozka.stav');
    }
}
