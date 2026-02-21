<?php

namespace App\Livewire\Doklad;

use App\Models\Doklad;
use App\Models\Printer; // Import modelu tiskárny
use App\Jobs\PrintLabelJob; // Import Jobu
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Mary\Traits\Toast;

class PrintDokladLabel extends Component
{
    use Toast;

    public $dokladId;
    public Doklad $doklad;

    // Formulářová data
    public $copies = 1;
    public $selectedPrinterId; // ID vybrané tiskárny

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
        // 1. Validace
        $this->validate([
            'selectedPrinterId' => 'required|exists:printers,id',
            'copies' => 'required|integer|min:1',
        ]);

        $this->modal = false;

        // 2. Příprava dat pro PDF
        $qrCode = base64_encode(QrCode::format('png')->size(200)->margin(0)->generate('https://jobtrack.cz?v=' . $this->dokladId));

        $data = [
            'id' => $this->dokladId,
            'projekt' => $this->doklad->MPSProjekt,
            'qrCode' => $qrCode,
            'author' => Auth::user()->name,
            'date' => date('d.m.Y H:i'),
        ];

        // Rozměr plátna PDF (aby to graficky sedělo na štítek)
        // Fyzický ořez řeší až Python služba podle nastavení v DB
        $customPaper = [0, 0, 170, 81];

        $pdf = Pdf::loadView('pdf.pdf-label', $data)
            ->setPaper($customPaper);

        // 3. Uložení PDF do Storage (aby ho Worker našel)
        // Použijeme unikátní název, aby se nepřepisovaly
        $filename = 'prints/label-' . $this->dokladId . '-' . time() . '.pdf';

        // Uložíme přímo obsah PDF do storage/app/prints/...
        Storage::put($filename, $pdf->output());

        // 4. Odeslání do FRONTY (Job)
        // Předáváme ID tiskárny, cestu k souboru a počet kopií
        PrintLabelJob::dispatch($this->selectedPrinterId, $filename, $this->copies);

        // 5. Info uživateli
        $this->success('Tisk byl zařazen do fronty.');
    }

    public function render()
    {
        // Načteme aktivní tiskárny pro selectbox
        // MaryUI select potřebuje kolekci objektů nebo pole ['id' => ..., 'name' => ...]
        $printers = Printer::where('is_active', true)->get();

        return view('livewire.doklad.print-doklad-label', [
            'printers' => $printers
        ]);
    }
}
