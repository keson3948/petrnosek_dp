<?php

namespace App\Livewire\Zasobovac;

use App\Jobs\PrintLabelJob;
use App\Models\EvPodsestav;
use App\Models\Polozka;
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

    public function boot()
    {
        abort_if(! auth()->user()->can('manage zasobovani'), 403);
    }

    public function mount($id)
    {
        $this->staDokl = StaDokl::with([
                'doklad.vlastniOsoba',
                'doklad.rodicZakazka.vlastniOsoba',
                'doklad.radky',
            ])
            ->where('Doklad', $id)
            ->typPohybu('EC_ZAKVYR')
            ->firstOrFail();

        $this->initNewEntries();
    }

    protected function initNewEntries(): void
    {
        $radky = $this->staDokl->doklad->radky;

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

        $nextPods = $radek->evPodsestavy->count() + 1;
        $oznaceni = $radek->CisloRadk . '.' . $nextPods;

        EvPodsestav::create([
            'ID' => EvPodsestav::nextId(),
            'VyrobniPrikaz' => $radek->SysPrimKlicDokladu,
            'EntitaRadkuVP' => $radek->EntitaRad,
            'Pozice' => $radek->Pozice,
            'OznaceniPodsestavy' => $oznaceni,
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
        $this->dispatch('entry-saved', rowIndex: $rowIndex);
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

    public function printLabel(int $evPodsId): void
    {
        $user = auth()->user();

        if (! $user->can('can print')) {
            $this->error('Nemáte oprávnění k tisku.');
            return;
        }

        if (! $user->printer_id) {
            $this->warning('Nemáte nastavenou preferovanou tiskárnu. Nastavte ji prosím v profilu.');
            return;
        }

        $printer = Printer::find($user->printer_id);

        if (! $printer || ! $printer->is_active) {
            $this->error('Vaše preferovaná tiskárna není aktivní. Změňte ji prosím v profilu.');
            return;
        }

        $evPods = EvPodsestav::find($evPodsId);

        if (! $evPods) {
            $this->error('Záznam nebyl nalezen.');
            return;
        }

        $radek = $this->staDokl->doklad->radky->first(
            fn ($r) => $r->EntitaRad == $evPods->EntitaRadkuVP
        );

        $mistrUser = $this->staDokl->doklad->vlastniOsoba?->user;

        $qrUrl = url('/qr') . '?p=' . $evPods->ID;

        $qrCode = base64_encode(
            QrCode::format('png')->size(200)->margin(0)
                ->generate($qrUrl)
        );

        $data = [
            'qrCode' => $qrCode,
            'mpsProjekt' => trim($this->staDokl->doklad->MPSProjekt ?? ''),
            'klicDokla' => trim($this->staDokl->doklad->KlicDokla ?? ''),
            'pozice' => trim($evPods->Pozice ?? '-'),
            'cisloPodsestavy' => trim($evPods->OznaceniPodsestavy ?? '-'),
            'mnozstvi' => (int) ($evPods->Mnozstvi ?? 1),
            'mistrCislo' => $mistrUser?->cislo_mistra,
        ];

        $customPaper = [0, 0, 170, 81];
        $pdf = Pdf::loadView('pdf.pdf-label', $data)->setPaper($customPaper);

        PrintLabelJob::dispatch($printer->id, base64_encode($pdf->output()), 1);

        $this->success('Tisk byl zařazen do fronty.');
    }

    public function render()
    {
        $radky = $this->staDokl->doklad->radky;

        $radky->load('evPodsestavy');

        $polozkyKeys = $radky->pluck('Material')
            ->merge($radky->pluck('PovrchoUp'))
            ->map(fn ($v) => trim($v ?? ''))
            ->filter()
            ->unique()
            ->values();

        $polozky = Polozka::whereIn('KlicPoloz', $polozkyKeys)->get()
            ->keyBy(fn ($p) => trim($p->KlicPoloz));

        foreach ($radky as $radek) {
            $radek->setRelation('materialPolozka', $polozky[trim($radek->Material ?? '')] ?? null);
            $radek->setRelation('povrchoUpPolozka', $polozky[trim($radek->PovrchoUp ?? '')] ?? null);
        }

        return view('livewire.zasobovac.show', [
            'radky' => $this->staDokl->doklad->radky,
            'mistrUser' => $this->staDokl->doklad->vlastniOsoba?->user,
        ]);
    }
}
