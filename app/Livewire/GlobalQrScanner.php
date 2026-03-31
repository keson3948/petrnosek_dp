<?php

namespace App\Livewire;

use App\Models\Doklad;
use App\Models\EvPodsestav;
use Livewire\Component;
use Livewire\Attributes\On;
use Mary\Traits\Toast;

class GlobalQrScanner extends Component
{
    use Toast;

    #[On('qr-scanned')]
    public function processQrCode($code)
    {
        $parsed = parse_url($code);

        if (isset($parsed['path']) && str_contains($parsed['path'], '/qr')) {
            parse_str($parsed['query'] ?? '', $params);

            if (isset($params['p'])) {
                $evPods = EvPodsestav::find((int) $params['p']);

                if ($evPods) {
                    return $this->redirectRoute('operace.podsestava', ['id' => $evPods->ID], navigate: true);
                }

                $this->error('Podsestava nebyla nalezena.');
                return;
            }
        }

        return $this->redirectRoute('qr.result', ['code' => base64_encode($code)], navigate: true);
    }

    public function render()
    {
        return view('livewire.global-qr-scanner');
    }
}
