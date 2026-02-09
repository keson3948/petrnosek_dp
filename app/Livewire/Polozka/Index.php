<?php

namespace App\Livewire\Polozka;

use App\Models\Polozka;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class Index extends Component
{
    public Collection $polozky;

    public function mount(Collection $polozky)
    {
        $this->polozky = $polozky;
    }

    public function render()
    {
        return view('livewire.polozka.index');
    }
}
