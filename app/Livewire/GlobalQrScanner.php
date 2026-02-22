<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Mary\Traits\Toast;

class GlobalQrScanner extends Component
{
    use Toast;

    #[On('qr-scanned')]
    public function processQrCode($code)
    {
        // Tuto metodu můžete dále rozšiřovat o přesměrování (redirect),
        // vyhledání v databázi, atd., podle toho co je obsahem kódu.
        
        $this->success("Naskenován QR kód: " . $code, timeout: 5000);
    }

    public function render()
    {
        return view('livewire.global-qr-scanner');
    }
}
