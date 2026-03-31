<?php

namespace App\Livewire\Operace;

use App\Models\DoklRadek;
use App\Models\Doklad;
use App\Models\EvPodsestav;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class PodsestavaShow extends Component
{
    public int $evPodsId;

    public function mount($id)
    {
        $this->evPodsId = (int) $id;
    }

    public function render()
    {
        $evPods = EvPodsestav::findOrFail($this->evPodsId);

        $doklad = Doklad::with('vlastniOsoba')
            ->where('SysPrimKlicDokladu', $evPods->VyrobniPrikaz)
            ->select(['SysPrimKlicDokladu', 'KlicDokla', 'MPSProjekt', 'VlastniOsoba'])
            ->first();

        $radek = $doklad
            ? DoklRadek::where('SysPrimKlicDokladu', $doklad->SysPrimKlicDokladu)
                ->where('EntitaRad', $evPods->EntitaRadkuVP)
                ->first()
            : null;

        $mistrUser = $doklad?->vlastniOsoba?->user;

        return view('livewire.operace.podsestava-show', [
            'evPods' => $evPods,
            'radek' => $radek,
            'doklad' => $doklad,
            'mistrUser' => $mistrUser,
        ]);
    }
}
