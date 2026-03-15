<?php

namespace App\Livewire\Doklad;

use App\Models\Doklad;
use App\Models\Printer;
use App\Jobs\PrintLabelJob;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Mary\Traits\Toast;

class PrintDokladLabel extends Component
{
    use Toast;

    public $dokladId;
    public Doklad $doklad;

    public $copies = 1;
    public $selectedPrinterId;

    public bool $modal = false;

    public function mount($dokladId, $doklad)
    {
        $this->dokladId = $dokladId;
        $this->doklad = $doklad;

        $printer = Printer::where('is_active', true)
            ->where('is_default', true)
            ->first();

        if (!$printer) {
            $printer = Printer::where('is_active', true)->first();
        }

        $this->selectedPrinterId = $printer?->id;
    }

    public function print()
    {
        $this->validate([
            'selectedPrinterId' => 'required|exists:printers,id',
            'copies' => 'required|integer|min:1',
        ]);

        $this->modal = false;

        $encodedId = str_replace('/', '-', $this->dokladId);
        $qrCode = base64_encode(QrCode::format('png')->size(200)->margin(0)->generate('https://jobtrack.cz?v=' . $encodedId . '&r=1&p=1'));

        $data = [
            'id' => $this->dokladId,
            'projekt' => $this->doklad->MPSProjekt,
            'qrCode' => $qrCode,
            'author' => Auth::user()->name,
            'date' => date('d.m.Y H:i'),
        ];

        $customPaper = [0, 0, 170, 81];

        $pdf = Pdf::loadView('pdf.pdf-label', $data)
            ->setPaper($customPaper);

        PrintLabelJob::dispatch($this->selectedPrinterId, base64_encode($pdf->output()), $this->copies);

        $this->success('Tisk byl zařazen do fronty.');
    }

    public function render()
    {
        $printers = Printer::where('is_active', true)->get();

        return view('livewire.doklad.print-doklad-label', [
            'printers' => $printers
        ]);
    }
}
