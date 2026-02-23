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
        return $this->redirectRoute('qr.result', ['code' => base64_encode($code)], navigate: true);
    }

    public function render()
    {
        return view('livewire.global-qr-scanner');
    }
}
