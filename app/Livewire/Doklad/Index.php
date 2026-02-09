<?php

namespace App\Livewire\Doklad;

use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class Index extends Component
{
    public Collection $staDoklady;

    // Definujeme hlavičky jako vlastnost komponenty
    public function headers(): array
    {
        return [
            ['key' => 'doklad.KlicDokla', 'label' => 'Klíč Dokladu'],
            ['key' => 'doklad.MPSProjekt', 'label' => 'MPS Projekt'],
            ['key' => 'doklad.vlastniOsoba.Prijmeni', 'label' => 'Vlastní Osoba'],
            ['key' => 'doklad.rodicZakazka.KlicDokla', 'label' => 'Zakázka (Klíč)'],
            ['key' => 'doklad.rodicZakazka.SpecifiSy', 'label' => 'Specifický Symbol'],
            ['key' => 'doklad.TerminDatum', 'label' => 'Termín Datum', 'format' => ['date', 'd.m.Y']],
            ['key' => 'Doklad', 'label'=>'ID', 'hidden' => true],
        ];
    }

    public function mount(Collection $staDoklady)
    {
        $this->staDoklady = $staDoklady;
    }

    public function showDetail($row)
    {
        $id = $row['Doklad'] ?? null;

        if (! $id) {
            // Log error nebo return, pokud by ID chybělo
            return;
        }

        return redirect()->route('stadokl.show', ['id' => trim($id)]);
    }

    public function render()
    {
        return view('livewire.doklad.index', [
            'headers' => $this->headers()
        ]);
    }
}
