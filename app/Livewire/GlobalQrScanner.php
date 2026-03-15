<?php

namespace App\Livewire;

use App\Models\Doklad;
use App\Models\StaDokl;
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

        if (isset($parsed['host']) && str_contains($parsed['host'], 'jobtrack.cz') && isset($parsed['query'])) {
            parse_str($parsed['query'], $params);

            if (isset($params['v'])) {
                $vValue = $params['v'];

                if (preg_match('/^(.+?)\s*r=/', $vValue, $m)) {
                    $vValue = rtrim($m[1]);
                }

                $dokladKey = str_replace('-', '/', trim($vValue));

                $staDokl = Doklad::where('KlicDokla', $dokladKey)
                    ->first();

                if ($staDokl) {
                    return $this->redirectRoute('stadokl.show', ['id' => $staDokl['SysPrimKlicDokladu']], navigate: true);
                }

                $this->error('Doklad "' . $dokladKey . '" nebyl nalezen.');
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
