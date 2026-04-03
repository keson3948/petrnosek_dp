<?php

namespace App\Livewire\Dashboard;

use App\Models\Doklad;
use App\Models\EvPodsestav;
use App\Models\PrednOperProstr;
use App\Models\PrednOsobProstr;
use App\Models\ProductionRecord;
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

    public string $edit_klicDokla = '';

    public string $vpSearch = '';

    // Machine / Operation edit
    public bool $showEditMachineOpModal = false;

    public ?string $edit_machine_id = '';

    public string $edit_operation_id = '';

    // Time edit
    public bool $showEditTimeModal = false;

    public $edit_hours = 0;

    public $edit_minutes = 0;

    public $edit_started_at = '';

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

    public $edit_quantity = 0;

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
        if (! $klicSubjektu) {
            return collect();
        }

        return PrednOsobProstr::forOsoba($klicSubjektu)
            ->with('prostredek')
            ->orderBy('Priorita')
            ->get()
            ->map(function ($r) {
                $r->machine_key = trim($r->Prrostredek ?? '');
                $r->machine_name = trim($r->prostredek?->NazevUplny ?? '');

                return $r;
            });
    }

    public function render()
    {
        $todayStart = now()->startOfDay();
        $fiveDaysAgo = now()->subDays(5)->startOfDay();

        $allCompleted = auth()->user()->productionRecords()
            ->with(['doklad.vlastniOsoba', 'machine', 'operation'])
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
        $this->edit_klicDokla = $record->doklad ? trim($record->doklad->KlicDokla) : '';
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

        return Doklad::dbcnt(10904)
            ->tdfDocType(410008)
            ->docYear(2022)
            ->where('ZakakaMPSJeUkoncena', 0)
            ->whereHas('staDoklad', fn ($q) => $q->where('TypPohybu', 'EC_ZAKVYR')->where('Vyhodnoceni', 1))
            ->where(fn ($q) => $q
                ->whereRaw('CAST("KlicDokla" AS VARCHAR(100)) LIKE ?', ["%{$term}%"])
                ->orWhereRaw('CAST("MPSProjekt" AS VARCHAR(100)) LIKE ?', ["%{$term}%"])
            )
            ->orderByDesc('KlicDokla')
            ->limit(8)
            ->get();
    }

    public function selectVp(string $klicDokla)
    {
        $this->edit_klicDokla = $klicDokla;
        $this->vpSearch = '';
    }

    public function clearVpSelection()
    {
        $this->edit_klicDokla = '';
    }

    public function saveEditVp()
    {
        if (! $this->edit_klicDokla) {
            $this->addError('vpSearch', 'Vyberte výrobní příkaz.');

            return;
        }

        $record = $this->findEditRecordForSave();

        $doklad = Doklad::dbcnt(10904)->tdfDocType(410008)->where('ZakakaMPSJeUkoncena', 0)->where('KlicDokla', $this->edit_klicDokla)->first();
        $newSysPrimKlic = $doklad ? trim($doklad->SysPrimKlicDokladu) : $this->edit_klicDokla;
        $oldSysPrimKlic = $record->ZakVP_SysPrimKlic;

        $updateData = [
            'ZakVP_SysPrimKlic' => $newSysPrimKlic,
            'SYSTIMEST' => now(),
        ];

        // VP se změnilo → resetovat řádek, podsestavu i výkres
        if ($newSysPrimKlic !== $oldSysPrimKlic) {
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

        return PrednOperProstr::forProstredek($this->edit_machine_id)
            ->with('operace')
            ->get()
            ->map(function ($r) {
                $r->operation_key = trim($r->Operace ?? '');
                $r->operation_name = trim($r->operace?->Nazev1 ?? '');

                return $r;
            });
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

        $doklad = Doklad::dbcnt(10904)->tdfDocType(410008)
            ->where('ZakakaMPSJeUkoncena', 0)
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
        $this->edit_drawing_only = $record->drawing_number ?? '';
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
        $this->edit_quantity = (int) ($record->processed_quantity ?? 0);
        $this->resetValidation();
        $this->showEditQuantityModal = true;
    }

    public function adjustQuantity(int $delta)
    {
        $this->edit_quantity = max(0, $this->edit_quantity + $delta);
    }

    public function saveEditQuantity()
    {
        $this->validate([
            'edit_quantity' => 'required|integer|min:0',
        ]);

        $record = $this->findEditRecordForSave();
        $record->update([
            'processed_quantity' => $this->edit_quantity,
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
        $this->edit_started_at = $record->started_at?->format('Y-m-d\TH:i');

        $workedMinutes = 0;
        if ($record->started_at && $record->ended_at) {
            $workedMinutes = max(0, intval($record->started_at->diffInMinutes($record->ended_at)) - ($record->total_paused_min ?? 0));
        }

        $this->edit_hours = intdiv($workedMinutes, 60);
        $this->edit_minutes = $workedMinutes % 60;

        $this->resetValidation();
        $this->showEditTimeModal = true;
    }

    public function adjustHours(int $delta)
    {
        $this->edit_hours = max(0, $this->edit_hours + $delta);
    }

    public function adjustMinutes(int $delta)
    {
        $newMinutes = $this->edit_minutes + $delta;
        if ($newMinutes >= 60) {
            $this->edit_hours++;
            $newMinutes -= 60;
        } elseif ($newMinutes < 0) {
            if ($this->edit_hours > 0) {
                $this->edit_hours--;
                $newMinutes += 60;
            } else {
                $newMinutes = 0;
            }
        }
        $this->edit_minutes = $newMinutes;
    }

    public function saveEditTime()
    {
        $record = $this->findEditRecordForSave();

        $workedMinutes = ($this->edit_hours * 60) + $this->edit_minutes;

        $updateData = [
            'started_at' => $this->edit_started_at,
            'SYSTIMEST' => now(),
        ];

        if ($this->edit_started_at) {
            $start = \Carbon\Carbon::parse($this->edit_started_at);
            $totalMinutes = $workedMinutes + ($record->total_paused_min ?? 0);
            $updateData['ended_at'] = $start->copy()->addMinutes($totalMinutes);
        }

        $record->update($updateData);

        $this->showEditTimeModal = false;
        $this->success('Čas uložen.');
    }
}
