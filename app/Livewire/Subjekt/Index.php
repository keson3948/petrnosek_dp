<?php

namespace App\Livewire\Subjekt;

use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class Index extends Component
{

    public Collection $subjekty;

    public function mount(Collection $subjekty)
    {
        $this->subjekty = $subjekty;
    }

    public function render()
    {
        return view('livewire.subjekt.index');
    }
}
