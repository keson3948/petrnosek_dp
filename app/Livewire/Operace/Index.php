<?php

namespace App\Livewire\Operace;

use App\Models\Operace;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]

class Index extends Component
{
    use WithPagination;

    public function render()
    {
        $operace = Operace::with(['stavDokladu', 'staPo'])
            ->where('KlicPoloz', '>=', '1000')
            ->orderBy('KlicPoloz')
            ->paginate(15);

        return view('livewire.operace.index', [
            'operace' => $operace,
        ]);
    }
}
