<?php

namespace App\Livewire\Zasobovac;

use App\Jobs\PrintLabelJob;
use App\Models\EvPodsestav;
use App\Models\Polozka;
use App\Models\Printer;
use App\Models\ProductionRecord;
use App\Models\StaDokl;
use App\Models\User;
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

    // History drawer state
    public bool $showHistoryDrawer = false;

    public string $historyDrawerTitle = '';

    public string $historyDrawerContext = '';

    public array $historyDrawerRecords = [];

    // Print modal state
    public bool $showPrintModal = false;
    public string $printType = '';  // 'doklad', 'radek', 'podsestava'
    public ?int $printTargetId = null;
    public int $printCopies = 1;

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

    public function openHistory(string $type, ?int $targetId = null, string $label = '', string $context = ''): void
    {
        $records = ProductionRecord::where('ZakVP_SysPrimKlic', trim($this->staDokl->Doklad))
            ->where('status', 2)
            ->with(['machine', 'operation'])
            ->orderBy('started_at');

        if ($type === 'vp') {
            $records = $records->whereNull('ZakVP_radek_entita')->get();
        } elseif ($type === 'radek') {
            $records = $records->where('ZakVP_radek_entita', $targetId)
                ->whereNull('ev_podsestav_id')->get();
        } elseif ($type === 'podsestava') {
            $records = $records->where('ev_podsestav_id', $targetId)->get();
        } else {
            $records = collect();
        }

        $userNames = User::whereIn('klic_subjektu',
                $records->pluck('user_id')->map(fn ($k) => trim($k))->filter()->unique()
            )->pluck('name', 'klic_subjektu')
            ->mapWithKeys(fn ($name, $k) => [trim($k) => $name])
            ->all();

        $this->historyDrawerTitle = $label ?: '';
        $this->historyDrawerContext = $context;

        $this->historyDrawerRecords = $records->map(function ($r) use ($userNames) {
            $totalMin = ($r->started_at && $r->ended_at)
                ? max(0, intval($r->started_at->diffInMinutes($r->ended_at)) - ($r->total_paused_min ?? 0))
                : null;

            return [
                'operation' => trim($r->operation?->Nazev1 ?? $r->operation_id ?? '') ?: '—',
                'started_at' => $r->started_at?->format('d.m. H:i') ?? '—',
                'operator' => $userNames[trim($r->user_id)] ?? '—',
                'machine' => trim($r->machine?->NazevUplny ?? $r->machine_id ?? '') ?: '—',
                'time' => $totalMin !== null ? intdiv($totalMin, 60) . ':' . str_pad($totalMin % 60, 2, '0', STR_PAD_LEFT) : '—',
                'quantity' => (int) ($r->processed_quantity ?? 0),
                'notes' => $r->notes ?: '',
            ];
        })->toArray();

        $this->showHistoryDrawer = true;
    }

    public function openPrintModal(string $type, ?int $id = null): void
    {
        $this->printType = $type;
        $this->printTargetId = $id;
        $this->printCopies = 1;
        $this->showPrintModal = true;
    }

    public function confirmPrint(): void
    {
        $this->validate([
            'printCopies' => 'required|integer|min:1|max:100',
        ]);

        $printer = $this->checkPrintPermissions();
        $doklad = $this->staDokl->doklad;
        $mistrUser = $doklad->vlastniOsoba?->user;
        $sysPrimKlic = trim($doklad->SysPrimKlicDokladu ?? '');

        match ($this->printType) {
            'doklad' => $this->printDokladLabel($doklad, $mistrUser, $sysPrimKlic, $printer),
            'radek' => $this->printRadekLabel($doklad, $mistrUser, $sysPrimKlic, $printer),
            default => $this->error('Neplatný typ tisku.'),
        };

        $this->showPrintModal = false;
    }

    protected function printDokladLabel($doklad, $mistrUser, string $sysPrimKlic, Printer $printer): void
    {
        $qrUrl = url('/qr') . '?d=' . $sysPrimKlic;

        $this->dispatchPrintJob($printer, $qrUrl, [
            'mpsProjekt' => trim($doklad->MPSProjekt ?? ''),
            'klicDokla' => trim($doklad->KlicDokla ?? ''),
            'pozice' => '',
            'cisloPodsestavy' => '',
            'mnozstvi' => '',
            'mistrCislo' => $mistrUser?->cislo_mistra,
        ]);
    }

    protected function printRadekLabel($doklad, $mistrUser, string $sysPrimKlic, Printer $printer): void
    {
        $radek = $doklad->radky->first(fn ($r) => $r->EntitaRad == $this->printTargetId);

        if (! $radek) {
            $this->error('Řádek nebyl nalezen.');
            return;
        }

        $qrUrl = url('/qr') . '?d=' . $sysPrimKlic . '.' . $radek->EntitaRad;

        $this->dispatchPrintJob($printer, $qrUrl, [
            'mpsProjekt' => trim($doklad->MPSProjekt ?? ''),
            'klicDokla' => trim($doklad->KlicDokla ?? ''),
            'cisloPodsestavy' => '',
            'pozice' => 'p.' . trim($radek->Pozice ?? '-'),
            'mnozstvi' => '',
            'mistrCislo' => $mistrUser?->cislo_mistra,
        ]);
    }

    public function printPodsestava(int $evPodsId): void
    {
        $this->printTargetId = $evPodsId;
        $this->printCopies = 1;

        $printer = $this->checkPrintPermissions();
        $doklad = $this->staDokl->doklad;
        $mistrUser = $doklad->vlastniOsoba?->user;

        $this->printPodsestavaLabel($doklad, $mistrUser, $printer);
    }

    protected function printPodsestavaLabel($doklad, $mistrUser, Printer $printer): void
    {
        $evPods = EvPodsestav::find($this->printTargetId);

        if (! $evPods) {
            $this->error('Podsestava nebyla nalezena.');
            return;
        }

        $qrUrl = url('/qr') . '?p=' . $evPods->ID;

        $this->dispatchPrintJob($printer, $qrUrl, [
            'mpsProjekt' => trim($doklad->MPSProjekt ?? ''),
            'klicDokla' => trim($doklad->KlicDokla ?? ''),
            'pozice' => 'p.'.trim($evPods->Pozice ?? '-'),
            'cisloPodsestavy' => 'v.'.trim($evPods->OznaceniPodsestavy ?? '-'),
            'mnozstvi' => (int) ($evPods->Mnozstvi ?? 1) . ' ks',
            'mistrCislo' => $mistrUser?->cislo_mistra,
        ]);

    }


    protected function checkPrintPermissions(): ?Printer
    {
        $user = auth()->user();

        if (! $user->can('can print')) {
            $this->error('Nemáte oprávnění k tisku.');
            return null;
        }

        if (! $user->printer_id) {
            $this->warning('Nemáte nastavenou preferovanou tiskárnu. Nastavte ji prosím v profilu.');
            return null;
        }

        $printer = Printer::find($user->printer_id);

        if (! $printer || ! $printer->is_active) {
            $this->error('Vaše preferovaná tiskárna není aktivní. Změňte ji prosím v profilu.');
            return null;
        }

        return $printer;
    }

    protected function dispatchPrintJob(Printer $printer, string $qrUrl, array $data): void
    {
        $qrCode = base64_encode(
            QrCode::format('svg')->size(200)->margin(0)->generate($qrUrl)
        );

        $data['qrCode'] = $qrCode;

        $customPaper = [0, 0, 175.7, 82.2];
        $pdf = Pdf::loadView('pdf.pdf-label', $data)->setPaper($customPaper);

        PrintLabelJob::dispatch($printer->id, base64_encode($pdf->output()), $this->printCopies);

        $this->success("Tisk {$this->printCopies}× byl zařazen do fronty.");
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

        // One lightweight query for history existence per level
        $historyRecords = ProductionRecord::where('ZakVP_SysPrimKlic', trim($this->staDokl->Doklad))
            ->where('status', 2)
            ->get(['ZakVP_radek_entita', 'ev_podsestav_id']);

        $vpHasHistory = $historyRecords->filter(fn ($r) => !$r->ZakVP_radek_entita)->isNotEmpty();
        $radekHasHistory = $historyRecords->filter(fn ($r) => $r->ZakVP_radek_entita && !$r->ev_podsestav_id)
            ->groupBy('ZakVP_radek_entita')->map->isNotEmpty();
        $podsestavaHasHistory = $historyRecords->filter(fn ($r) => $r->ev_podsestav_id)
            ->groupBy('ev_podsestav_id')->map->isNotEmpty();

        return view('livewire.zasobovac.show', [
            'radky' => $this->staDokl->doklad->radky,
            'mistrUser' => $this->staDokl->doklad->vlastniOsoba?->user,
            'backRoute' => route('zasobovac.index'),
            'vpHasHistory' => $vpHasHistory,
            'radekHasHistory' => $radekHasHistory,
            'podsestavaHasHistory' => $podsestavaHasHistory,
        ]);
    }
}
