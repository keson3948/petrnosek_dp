<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class QrScannerResult extends Component
{
    public string $code = '';

    public function mount($code)
    {
        $this->code = base64_decode($code);
    }

    public function render()
    {
        return view('livewire.qr-scanner-result');
    }
}
