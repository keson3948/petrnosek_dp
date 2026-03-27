<?php

namespace App\Livewire\Zasobovac;

use App\Jobs\PrintLabelJob;
use App\Models\EvPodsestav;
use App\Models\Printer;
use App\Models\StaDokl;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Mary\Traits\Toast;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

#[Layout('layouts.app')]
class Show extends Component
{
    use Toast;

    public StaDokl $staDokl;

    public array $newEntries = [];

    public ?int $editingId = null;
    public array $editEntry = ['CisloVykresu' => '', 'Mnozstvi' => '', 'Poznamka' => ''];

    public bool $printModal = false;
    public ?int $printEvPodsId = null;
    public $selectedPrinterId;
    public int $copies = 1;

    public function boot()
    {
        abort_if(! auth()->user()->can('manage zasobovani'), 403);
    }

    public function mount($id)
    {
        $this->staDokl = StaDokl::with(['doklad.vlastniOsoba', 'doklad.rodicZakazka.vlastniOsoba'])
            ->where('Doklad', $id)
            ->typPohybu('EC_ZAKVYR')
            ->firstOrFail();

        $this->initNewEntries();

        $printer = Printer::where('is_active', true)->where('is_default', true)->first()
            ?? Printer::where('is_active', true)->first();
        $this->selectedPrinterId = $printer?->id;
    }

    protected function initNewEntries(): void
    {
        $radky = $this->staDokl->doklad->radky ?? collect();

        foreach ($radky as $index => $radek) {
            if (! isset($this->newEntries[$index])) {
                $this->newEntries[$index] = [
                    'CisloVykresu' => '',
                    'Mnozstvi' => '',
                    'Poznamka' => '',
                ];
            }
        }
    }

    public function saveEntry(int $rowIndex): void
    {
        $this->validate([
            "newEntries.{$rowIndex}.CisloVykresu" => 'required|string|max:100',
            "newEntries.{$rowIndex}.Mnozstvi" => 'required|numeric|min:0.01',
            "newEntries.{$rowIndex}.Poznamka" => 'nullable|string|max:255',
        ]);

        $radky = $this->staDokl->doklad->radky;
        $radek = $radky[$rowIndex];
        $entry = $this->newEntries[$rowIndex];
        $now = now()->format('Y-m-d H:i:s');

        EvPodsestav::create([
            'ID' => EvPodsestav::nextId(),
            'VyrobniPrikaz' => $radek->SysPrimKlicDokladu,
            'EntitaRadkuVP' => $radek->EntitaRad,
            'Pozice' => $radek->Pozice,
            'CisloVykresu' => $entry['CisloVykresu'],
            'Mnozstvi' => (float) $entry['Mnozstvi'],
            'Poznamka' => $entry['Poznamka'] ?: null,
            'CTSMP' => $now,
            'SYSTIMEST' => $now,
        ]);

        $this->newEntries[$rowIndex] = [
            'CisloVykresu' => '',
            'Mnozstvi' => '',
            'Poznamka' => '',
        ];

        $this->success('Záznam uložen.');
    }

    public function startEdit(int $evPodsId): void
    {
        $ev = EvPodsestav::find($evPodsId);
        if (! $ev) return;

        $this->editingId = $evPodsId;
        $this->editEntry = [
            'CisloVykresu' => trim($ev->CisloVykresu ?? ''),
            'Mnozstvi' => $ev->Mnozstvi,
            'Poznamka' => trim($ev->Poznamka ?? ''),
        ];
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->editEntry = ['CisloVykresu' => '', 'Mnozstvi' => '', 'Poznamka' => ''];
    }

    public function updateEntry(): void
    {
        $this->validate([
            'editEntry.CisloVykresu' => 'required|string|max:100',
            'editEntry.Mnozstvi' => 'required|numeric|min:0.01',
            'editEntry.Poznamka' => 'nullable|string|max:255',
        ]);

        EvPodsestav::where('ID', $this->editingId)->update([
            'CisloVykresu' => $this->editEntry['CisloVykresu'],
            'Mnozstvi' => (float) $this->editEntry['Mnozstvi'],
            'Poznamka' => $this->editEntry['Poznamka'] ?: null,
            'SYSTIMEST' => now()->format('Y-m-d H:i:s'),
        ]);

        $this->editingId = null;
        $this->editEntry = ['CisloVykresu' => '', 'Mnozstvi' => '', 'Poznamka' => ''];
        $this->success('Záznam upraven.');
    }

    public function deleteEntry(int $evPodsId): void
    {
        EvPodsestav::where('ID', $evPodsId)->delete();
        $this->success('Záznam smazán.');
    }

    public function openPrintModal(int $evPodsId): void
    {
        $this->printEvPodsId = $evPodsId;
        $this->copies = 1;
        $this->printModal = true;
    }

    public function printLabel(): void
    {
        $this->validate([
            'selectedPrinterId' => 'required|exists:printers,id',
            'copies' => 'required|integer|min:1',
        ]);

        $this->printModal = false;

        $evPods = EvPodsestav::find($this->printEvPodsId);

        $qrCode = base64_encode(
            QrCode::format('png')->size(200)->margin(0)
                ->generate("https://jobtrack.cz?v={$this->printEvPodsId}&r=1&p=1")
        );

        $data = [
            'id' => $this->printEvPodsId,
            'projekt' => $evPods->CisloVykresu ?? '',
            'qrCode' => $qrCode,
            'author' => auth()->user()->name,
            'date' => date('d.m.Y H:i'),
        ];

        $customPaper = [0, 0, 170, 81];
        $pdf = Pdf::loadView('pdf.pdf-label', $data)->setPaper($customPaper);

        PrintLabelJob::dispatch($this->selectedPrinterId, base64_encode($pdf->output()), $this->copies);

        $this->success('Tisk byl zařazen do fronty.');
    }

    public function render()
    {
        $radky = $this->staDokl->doklad->radky()->with(['evPodsestavy', 'materialPolozka', 'povrchoUpPolozka'])->get();

        return view('livewire.zasobovac.show', [
            'radky' => $radky,
            'printers' => Printer::where('is_active', true)->get(),
            'mistrUser' => $this->staDokl->doklad->vlastniOsoba?->user,
        ]);
    }
}
