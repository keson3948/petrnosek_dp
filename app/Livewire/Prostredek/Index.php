<?php

namespace App\Livewire\Prostredek;

use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class Index extends Component
{

    public Collection $prostredky;

    public function mount(Collection $prostredky)
    {
        $this->$prostredky = $prostredky;
    }

    public function render()
    {
        return view('livewire.prostredek.index');
    }
}
