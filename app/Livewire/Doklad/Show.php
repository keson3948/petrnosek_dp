<?php

namespace App\Livewire\Doklad;

use App\Models\StaDokl;
use Livewire\Component;

class Show extends Component
{
    public StaDokl $staDokl;

    public function mount(StaDokl $staDokl)
    {
        $this->staDokl = $staDokl;
    }

    // Definice hlaviček pro tabulku položek
    public function headers(): array
    {
        return [
            ['key' => 'CisloRadk', 'label' => 'Č. řádku'],
            ['key' => 'Polozka', 'label' => 'Položka'],
            ['key' => 'TxtRadku', 'label' => 'Text'],
            ['key' => 'MnozstviZMJ', 'label' => 'Množství', 'class' => 'text-right'], // Zarovnání doprava
            ['key' => 'ProdCeZaZMJvM1D', 'label' => 'Cena', 'class' => 'text-right'],
        ];
    }

    public function render()
    {
        return view('livewire.doklad.show', [
            'radky' => $this->staDokl->doklad->radky ?? []
        ]);
    }
}
