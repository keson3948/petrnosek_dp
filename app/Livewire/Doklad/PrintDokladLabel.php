<?php

namespace App\Livewire\Doklad;

use App\Models\Doklad;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PrintDokladLabel extends Component
{
    public $dokladId;

    public Doklad $doklad;

    public $copies = 1;

    public $showModal = false;

    public $statusMessage = '';

    public $messageType = ''; // 'success' or 'error'

    public function mount($dokladId, $doklad)
    {
        $this->dokladId = $dokladId;
        $this->doklad = $doklad;
    }

    public function print()
    {
        if (! $this->dokladId) {
            abort(404, 'Doklad ID missing');
        }

        $qrCode = base64_encode(QrCode::format('png')->size(200)->margin(0)->generate('https://jobtrack.cz?v=1234567890&r=123&p=123'));

        $data = [
            'id' => $this->dokladId,
            'projekt' => $this->doklad->MPSProjekt,
            'qrCode' => $qrCode,
            'author' => Auth::user()->name,
            'date' => date('d.m.Y H:i'),
        ];

        // custom paper size 69mm x 29 mm
        $customPaper = [0, 0, 170, 81];

        $pdf = Pdf::loadView('pdf.pdf-label', $data)
            ->setPaper($customPaper);

        $safeId = str_replace(['/', '\\'], '-', $this->dokladId);

        // Ujisti se, že adresář existuje
        Storage::disk('local')->makeDirectory('public/labels');

        // Relativní cesta v storage/app
        $relativePath = 'public/labels/label-'.$safeId.'.pdf';

        // Absolutní cesta (pro tiskovou službu)
        $absolutePath = storage_path('app/'.$relativePath);

        $pdf->save($absolutePath);

        // echo $absolutePath; // Livewire nesmí mít echo output

        $response = Http::withHeaders([
            'Accept' => 'application/json',
        ])
            ->attach(
                'file',
                file_get_contents($absolutePath),
                basename($absolutePath)
            )
            ->post(config('services.print.url').'/print', [
                'printer' => 'default',
            ]);

        if ($response->successful()) {
            session()->flash('success', 'Štítek byl odeslán k tisku.');
        } else {
            session()->flash('error', 'Chyba při tisku: '.$response->body());
        }

    }

    public function render()
    {
        return view('livewire.doklad.print-doklad-label');
    }
}
