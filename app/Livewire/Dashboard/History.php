<?php

namespace App\Livewire\Dashboard;

use App\Models\Doklad;
use App\Models\EvPodsestav;
use App\Models\PrednOperProstr;
use App\Models\PrednOsobProstr;
use App\Models\ProductionRecord;
use App\Models\Prostredek;
use App\Models\Terminal;
use Livewire\Attributes\On;
use Livewire\Component;
use Mary\Traits\Toast;

class History extends Component
{
    use Toast;

    // --- Edit Modals ---
    public ?int $editRecordId = null;

    // VP edit
    public bool $showEditVpModal = false;

    public string $edit_vp_sysPrimKlic = '';

    public string $edit_vp_label = '';

    public string $vpSearch = '';

    // Machine / Operation edit
    public bool $showEditMachineOpModal = false;

    public ?string $edit_machine_id = '';

    public string $edit_operation_id = '';

    // Time edit
    public bool $showEditTimeModal = false;

    public $edit_time_init = [];

    public $mistriDict = [];

    // Řádek / Podsestava edit
    public bool $showEditRadekPodsModal = false;

    public ?int $edit_radek_entita = null;

    public ?int $edit_ev_podsestav_id = null;

    public ?string $edit_pozice_radku = null;

    public ?string $edit_drawing_number = null;

    public ?string $edit_sysPrimKlic = null;

    // Výkres edit
    public bool $showEditDrawingModal = false;

    public string $edit_drawing_only = '';

    // Množství edit
    public bool $showEditQuantityModal = false;

    public int $edit_quantity_init = 0;

    // Poznámka edit
    public bool $showEditNotesModal = false;

    public string $edit_notes = '';

    #[On('operation-completed')]
    public function refresh(): void
    {
        // Livewire will re-render automatically
    }

    public function getUserMachinesProperty()
    {
        $klicSubjektu = auth()->user()->klic_subjektu;
        $terminal = Terminal::current();
        $pracovisteFilter = $terminal?->klic_pracoviste;

        $assigned = collect();
        if ($klicSubjektu) {
            $assigned = PrednOsobProstr::forOsoba($klicSubjektu)
                ->with('prostredek')
                ->orderBy('Priorita')
                ->get();
        }

        $allAssignedMachineKeys = PrednOsobProstr::pluck('Prrostredek')
            ->map(fn ($k) => trim($k))
            ->unique()
            ->values();

        $unassignedQuery = Prostredek::dbcnt(730550)
            ->where('KlicProstredku', 'like', '20%')
            ->whereNotIn('KlicProstredku', $allAssignedMachineKeys);

        if ($pracovisteFilter) {
            $unassignedQuery->where('Pracoviste', $pracovisteFilter);
        }

        $unassigned = $unassignedQuery->orderBy('KlicProstredku')->get();

        $machines = collect();

        foreach ($assigned as $r) {
            $prostredek = $r->prostredek;
            if ($pracovisteFilter && trim($prostredek?->Pracoviste ?? '') !== $pracovisteFilter) {
                continue;
            }
            $r->machine_key = trim($r->Prrostredek ?? '');
            $r->machine_name = trim($prostredek?->NazevUplny ?? '');
            $machines->push($r);
        }

        foreach ($unassigned as $prostredek) {
            $obj = (object) [
                'machine_key' => trim($prostredek->KlicProstredku),
                'machine_name' => trim($prostredek->NazevUplny ?? ''),
                'prostredek' => $prostredek,
            ];
            $machines->push($obj);
        }

        return $machines;
    }

    public function render()
    {
        $todayStart = now()->startOfDay();
        $fiveDaysAgo = now()->subDays(5)->startOfDay();

        $allCompleted = auth()->user()->productionRecords()
            ->with(['doklad.vlastniOsoba', 'machine', 'operation', 'podsestav'])
            ->where('status', 2)
            ->where('ended_at', '>=', $fiveDaysAgo)
            ->orderByDesc('ended_at')
            ->get();

        $mistrKeys = $allCompleted->map(fn ($r) => trim($r->doklad?->VlastniOsoba ?? ''))->filter()->unique();
        if ($mistrKeys->isNotEmpty()) {
            $this->mistriDict = \App\Models\User::whereIn('klic_subjektu', $mistrKeys)
                ->get()
                ->keyBy(fn ($u) => trim($u->klic_subjektu))
                ->all();
        }

        $today = $allCompleted->filter(fn ($r) => $r->ended_at->isToday())->values();
        $historical = $allCompleted->filter(fn ($r) => $r->ended_at < $todayStart);

        return view('livewire.dashboard.history', [
            'today' => $today,
            'historical' => $historical,
        ]);
    }

    public function getRecordInfo(ProductionRecord $record): array
    {
        $workedH = null;
        $workedM = null;

        if ($record->started_at && $record->ended_at) {
            $totalMinutes = max(0, intval($record->started_at->diffInMinutes($record->ended_at)) - ($record->total_paused_min ?? 0));
            $workedH = intdiv($totalMinutes, 60);
            $workedM = $totalMinutes % 60;
        }

        $mistrKey = trim($record->doklad?->VlastniOsoba ?? '');
        $mistr = $mistrKey ? ($this->mistriDict[$mistrKey] ?? null) : null;

        return [
            'workedH' => $workedH,
            'workedM' => $workedM,
            'mistrColor' => $mistr?->color ?? 'gray',
            'mistrCislo' => $mistr?->cislo_mistra ?? '?',
        ];
    }

    // ==========================================
    // Helper – find record safely
    // ==========================================

    private function findEditRecord(int $id): ProductionRecord
    {
        return auth()->user()->productionRecords()->findOrFail($id);
    }

    private function findEditRecordForSave(): ProductionRecord
    {
        return ProductionRecord::where('user_id', auth()->user()->klic_subjektu)->findOrFail($this->editRecordId);
    }

    // ==========================================
    // VP Edit (search + select only)
    // ==========================================

    public function openEditVp(int $id)
    {
        $record = $this->findEditRecord($id);
        $this->editRecordId = (int) $record->ID;
        $this->edit_vp_sysPrimKlic = $record->ZakVP_SysPrimKlic ?? '';
        $this->edit_vp_label = $record->doklad ? trim($record->doklad->KlicDokla) : '';
        $this->vpSearch = '';
        $this->resetValidation();
        $this->showEditVpModal = true;
    }

    public function getVpSearchResultsProperty()
    {
        if (mb_strlen(trim($this->vpSearch)) < 2) {
            return collect();
        }

        $term = mb_substr(mb_strtoupper(trim($this->vpSearch)), 0, 10);

        return Doklad::allTypes()
            ->searchByTerm($term)
            ->orderByDesc('KlicDokla')
            ->limit(12)
            ->get();
    }

    public function selectVp(string $sysPrimKlic, string $label)
    {
        $this->edit_vp_sysPrimKlic = $sysPrimKlic;
        $this->edit_vp_label = $label;
        $this->vpSearch = '';
    }

    public function clearVpSelection()
    {
        $this->edit_vp_sysPrimKlic = '';
        $this->edit_vp_label = '';
    }

    public function saveEditVp()
    {
        if (! $this->edit_vp_sysPrimKlic) {
            $this->addError('vpSearch', 'Vyberte výrobní příkaz.');

            return;
        }

        $dokladExists = Doklad::allTypes()
            ->where('SysPrimKlicDokladu', $this->edit_vp_sysPrimKlic)
            ->exists();

        if (! $dokladExists) {
            $this->addError('vpSearch', 'Vybraný výrobní příkaz neexistuje.');

            return;
        }

        $record = $this->findEditRecordForSave();
        $oldSysPrimKlic = $record->ZakVP_SysPrimKlic;

        $updateData = [
            'ZakVP_SysPrimKlic' => $this->edit_vp_sysPrimKlic,
            'SYSTIMEST' => now(),
        ];

        // VP se změnilo → resetovat řádek, podsestavu i výkres
        if ($this->edit_vp_sysPrimKlic !== $oldSysPrimKlic) {
            $updateData['ZakVP_radek_entita'] = null;
            $updateData['ZakVP_pozice_radku'] = null;
            $updateData['ev_podsestav_id'] = null;
            $updateData['drawing_number'] = null;
        }

        $record->update($updateData);

        $this->showEditVpModal = false;
        $this->success('VP uložen.');
    }

    // ==========================================
    // Machine / Operation Edit
    // ==========================================

    public function openEditMachineOp(int $id)
    {
        $record = $this->findEditRecord($id);
        $this->editRecordId = (int) $record->ID;
        $this->edit_machine_id = $record->machine_id ?? '';
        $this->edit_operation_id = $record->operation_id ?? '';
        $this->resetValidation();
        $this->showEditMachineOpModal = true;
    }

    public function selectMachine(string $machineKey)
    {
        $this->edit_machine_id = $machineKey;
        $operations = PrednOperProstr::forProstredek($machineKey)->get();
        if ($operations->count() === 1) {
            $this->edit_operation_id = trim($operations->first()->Operace);
        } else {
            $this->edit_operation_id = '';
        }
    }

    public function selectOperation(string $operationKey)
    {
        $this->edit_operation_id = $operationKey;
    }

    public function saveEditMachineOp()
    {
        $this->validate([
            'edit_machine_id' => 'nullable|string|max:255',
            'edit_operation_id' => 'required|string|max:255',
        ]);

        if ($this->edit_machine_id) {
            $machineExists = $this->userMachines->contains('machine_key', $this->edit_machine_id);

            if (! $machineExists) {
                $this->addError('edit_machine_id', 'Vybraný stroj neexistuje nebo k němu nemáte přístup.');

                return;
            }

            $operationExists = $this->machineOperations->contains('operation_key', $this->edit_operation_id);

            if (! $operationExists) {
                $this->addError('edit_operation_id', 'Vybraná operace nepatří ke zvolenému stroji.');

                return;
            }
        }

        $record = $this->findEditRecordForSave();
        $record->update([
            'machine_id' => $this->edit_machine_id,
            'operation_id' => $this->edit_operation_id,
            'SYSTIMEST' => now(),
        ]);

        $this->showEditMachineOpModal = false;
        $this->success('Stroj a operace uloženy.');
    }

    public function getMachineOperationsProperty()
    {
        if (! $this->edit_machine_id) {
            return collect();
        }

        $assigned = PrednOperProstr::forProstredek($this->edit_machine_id)
            ->with('operace')
            ->get()
            ->map(function ($r) {
                $r->operation_key = trim($r->Operace ?? '');
                $r->operation_name = trim($r->operace?->Nazev1 ?? '');

                return $r;
            });

        $unassigned = PrednOperProstr::where('Prostredek', '~')
            ->with('operace')
            ->get()
            ->map(function ($r) {
                $r->operation_key = trim($r->Operace ?? '');
                $r->operation_name = trim($r->operace?->Nazev1 ?? '');

                return $r;
            });

        return $assigned->concat($unassigned);
    }

    // ==========================================
    // Řádek / Podsestava Edit
    // ==========================================

    public function openEditRadekPodsestava(int $id)
    {
        $record = $this->findEditRecord($id);
        $this->editRecordId = (int) $record->ID;
        $this->edit_sysPrimKlic = $record->ZakVP_SysPrimKlic;
        $this->edit_radek_entita = $record->ZakVP_radek_entita ? (int) $record->ZakVP_radek_entita : null;
        $this->edit_ev_podsestav_id = $record->ev_podsestav_id ? (int) $record->ev_podsestav_id : null;
        $this->edit_pozice_radku = $record->ZakVP_pozice_radku;
        $this->edit_drawing_number = $record->drawing_number;
        $this->resetValidation();
        $this->showEditRadekPodsModal = true;
    }

    public function getEditRadkyProperty()
    {
        if (! $this->edit_sysPrimKlic) {
            return collect();
        }

        $doklad = Doklad::allTypes()
            ->where('SysPrimKlicDokladu', $this->edit_sysPrimKlic)
            ->with(['radky.materialPolozka', 'radky.evPodsestavy'])
            ->first();

        return $doklad ? $doklad->radky : collect();
    }

    public function getEditPodsestavyProperty()
    {
        if (! $this->edit_radek_entita) {
            return collect();
        }

        return EvPodsestav::where('EntitaRadkuVP', $this->edit_radek_entita)->get();
    }

    public function editSelectRadek(int $entitaRad)
    {
        $this->edit_radek_entita = $entitaRad;
        // Změna řádku → resetovat podsestavu a výkres
        $this->edit_ev_podsestav_id = null;
        $this->edit_drawing_number = null;

        $radek = $this->editRadky->firstWhere('EntitaRad', $entitaRad);
        $this->edit_pozice_radku = $radek ? $radek->Pozice : null;
    }

    public function editClearRadek()
    {
        $this->edit_radek_entita = null;
        $this->edit_ev_podsestav_id = null;
        $this->edit_pozice_radku = null;
        $this->edit_drawing_number = null;
    }

    public function editSelectPodsestava(int $id)
    {
        $this->edit_ev_podsestav_id = $id;
        $evPods = EvPodsestav::find($id);
        if ($evPods) {
            // Podsestava se změnila → aktualizovat výkres z podsestavy
            $this->edit_drawing_number = trim($evPods->CisloVykresu ?? '');
        }
    }

    public function editClearPodsestava()
    {
        $this->edit_ev_podsestav_id = null;
        $this->edit_drawing_number = null;
    }

    public function saveEditRadekPodsestava()
    {
        $record = $this->findEditRecordForSave();

        if ($this->edit_radek_entita) {
            $doklad = Doklad::allTypes()
                ->where('SysPrimKlicDokladu', $record->ZakVP_SysPrimKlic)
                ->with('radky')
                ->first();

            if (! $doklad || ! $doklad->radky->contains('EntitaRad', $this->edit_radek_entita)) {
                $this->addError('edit_radek_entita', 'Vybraný řádek nepatří k tomuto výrobnímu příkazu.');

                return;
            }
        }

        if ($this->edit_ev_podsestav_id) {
            if (! $this->edit_radek_entita) {
                $this->addError('edit_ev_podsestav_id', 'Nelze zadat podsestavu bez vybraného řádku.');

                return;
            }

            $podsestavExists = EvPodsestav::where('ID', $this->edit_ev_podsestav_id)
                ->where('EntitaRadkuVP', $this->edit_radek_entita)
                ->exists();

            if (! $podsestavExists) {
                $this->addError('edit_ev_podsestav_id', 'Vybraná podsestava nepatří k tomuto řádku.');

                return;
            }
        }

        $record->update([
            'ZakVP_radek_entita' => $this->edit_radek_entita,
            'ZakVP_pozice_radku' => $this->edit_pozice_radku,
            'ev_podsestav_id' => $this->edit_ev_podsestav_id,
            'drawing_number' => $this->edit_drawing_number,
            'SYSTIMEST' => now(),
        ]);

        $this->showEditRadekPodsModal = false;
        $this->success('Řádek a podsestava uloženy.');
    }

    // ==========================================
    // Výkres Edit
    // ==========================================

    public function openEditDrawing(int $id)
    {
        $record = $this->findEditRecord($id);
        $this->editRecordId = (int) $record->ID;
        $this->edit_drawing_only = trim($record->drawing_number) ?? '';
        $this->resetValidation();
        $this->showEditDrawingModal = true;
    }

    public function saveEditDrawing()
    {
        $record = $this->findEditRecordForSave();
        $record->update([
            'drawing_number' => $this->edit_drawing_only ?: null,
            'SYSTIMEST' => now(),
        ]);

        $this->showEditDrawingModal = false;
        $this->success('Výkres uložen.');
    }

    // ==========================================
    // Množství Edit
    // ==========================================

    public function openEditQuantity(int $id)
    {
        $record = $this->findEditRecord($id);
        $this->editRecordId = (int) $record->ID;
        $this->edit_quantity_init = (int) ($record->processed_quantity ?? 0);
        $this->resetValidation();
        $this->showEditQuantityModal = true;
    }

    public function saveEditQuantity(int $quantity)
    {
        if ($quantity < 0) {
            $this->addError('edit_quantity', 'Množství nesmí být záporné.');

            return;
        }

        $record = $this->findEditRecordForSave();
        $record->update([
            'processed_quantity' => $quantity,
            'SYSTIMEST' => now(),
        ]);

        $this->showEditQuantityModal = false;
        $this->success('Množství uloženo.');
    }

    // ==========================================
    // Poznámka Edit
    // ==========================================

    public function openEditNotes(int $id)
    {
        $record = $this->findEditRecord($id);
        $this->editRecordId = (int) $record->ID;
        $this->edit_notes = $record->notes ?? '';
        $this->resetValidation();
        $this->showEditNotesModal = true;
    }

    public function saveEditNotes()
    {
        $record = $this->findEditRecordForSave();
        $record->update([
            'notes' => $this->edit_notes ?: null,
            'SYSTIMEST' => now(),
        ]);

        $this->showEditNotesModal = false;
        $this->success('Poznámka uložena.');
    }

    // ==========================================
    // Time Edit
    // ==========================================

    public function openEditTime(int $id)
    {
        $record = $this->findEditRecord($id);
        $this->editRecordId = (int) $record->ID;

        $workedMinutes = 0;
        if ($record->started_at && $record->ended_at) {
            $workedMinutes = max(0, intval($record->started_at->diffInMinutes($record->ended_at)) - ($record->total_paused_min ?? 0));
        }

        $this->edit_time_init = [
            'started_at' => $record->started_at?->format('Y-m-d\TH:i') ?? '',
            'hours' => intdiv($workedMinutes, 60),
            'minutes' => $workedMinutes % 60,
        ];

        $this->resetValidation();
        $this->showEditTimeModal = true;
    }

    public function saveEditTime($hours, $minutes, $startedAt)
    {
        $hours = (int) $hours;
        $minutes = (int) $minutes;
        $startedAt = (string) $startedAt;

        if ($hours < 0 || $minutes < 0) {
            $this->addError('edit_time', 'Hodiny a minuty nesmí být záporné.');

            return;
        }

        $workedMinutes = ($hours * 60) + $minutes;

        if ($workedMinutes == 0) {
            $this->addError('edit_time', 'Odpracovaný čas musí být větší než 0.');

            return;
        }

        if (! $startedAt) {
            $this->addError('edit_time', 'Začátek práce musí být vyplněn.');

            return;
        }

        $start = \Carbon\Carbon::parse($startedAt);

        if ($start->isFuture()) {
            $this->addError('edit_time', 'Začátek práce nesmí být v budoucnosti.');

            return;
        }

        $record = $this->findEditRecordForSave();

        $totalMinutes = $workedMinutes + ($record->total_paused_min ?? 0);
        $endedAt = $start->copy()->addMinutes($totalMinutes);

        if ($endedAt->isFuture()) {
            $this->addError('edit_time', 'Konec práce nesmí být v budoucnosti.');

            return;
        }

        $record->update([
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
            'SYSTIMEST' => now(),
        ]);

        $this->showEditTimeModal = false;
        $this->success('Čas uložen.');
    }
}
