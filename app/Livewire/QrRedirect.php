<?php

namespace App\Livewire;

use App\Models\EvPodsestav;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class QrRedirect extends Component
{
    public function mount()
    {
        $p = request()->query('p');

        if ($p) {
            $evPods = EvPodsestav::find((int) $p);

            if ($evPods) {
                return $this->redirectRoute('operace.podsestava', ['id' => $evPods->ID], navigate: true);
            }

            session()->flash('error', 'Podsestava nebyla nalezena.');
            return $this->redirectRoute('dashboard', navigate: true);
        }

        abort(404);
    }

    public function render()
    {
        return view('livewire.qr-redirect');
    }
}
