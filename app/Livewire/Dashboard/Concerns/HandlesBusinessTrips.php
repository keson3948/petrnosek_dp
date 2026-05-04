<?php

namespace App\Livewire\Dashboard\Concerns;

use App\Models\ProductionRecord;
use App\Models\SluzebniCesta;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;

trait HandlesBusinessTrips
{
    public bool $showTripConfirmation = false;

    public ?string $selectedTripKey = null;

    #[On('open-start-drawer-trip')]
    public function openStartDrawerWithTrip(string $tripKey): void
    {
        $this->openStartDrawer();
        $this->selectTrip($tripKey);
    }

    public function selectTrip(string $klicSluzebniCesty): void
    {
        $this->selectedTripKey = $klicSluzebniCesty;
        $this->showTripConfirmation = true;
    }

    public function cancelTrip(): void
    {
        $this->selectedTripKey = null;
        $this->showTripConfirmation = false;
    }

    public function startTripOperation(): void
    {
        $trip = $this->selectedTrip;
        if (! $trip) {
            $this->error('Služební cesta nebyla nalezena.');

            return;
        }

        $hasActive = auth()->user()->productionRecords()
            ->work()
            ->whereIn('status', [0, 1])
            ->exists();

        if ($hasActive) {
            $this->error('Již máte aktivní nebo pozastavený záznam.');

            return;
        }

        $nextId = ProductionRecord::nextId();

        $skupinaKlic = trim(auth()->user()->employeeGroup()?->KlicSkupinyZamestnancu ?? '') ?: null;

        ProductionRecord::create([
            'ID' => $nextId,
            'machine_id' => null,
            'user_id' => auth()->user()->klic_subjektu,
            'started_at' => now(),
            'pracoviste_id' => trim($trip->MistoRealizacePracoviste ?? '') ?: null,
            'operation_id' => trim($trip->HlavniCinnost ?? '') ?: null,
            'ZakVP_SysPrimKlic' => trim($trip->ZakazkaVyrobniPrikaz ?? '') ?: null,
            'drawing_number' => null,
            'ev_podsestav_id' => null,
            'ZakVP_radek_entita' => null,
            'ZakVP_pozice_radku' => null,
            'SluzebniCesta' => 1,
            'status' => 0,
            'SkupinaZamestnancu' => $skupinaKlic,
            'CTSMP' => now(),
            'SYSTIMEST' => now(),
        ]);

        $this->showStartDrawer = false;
        $this->dispatch('operation-started');
        $this->success('Služební cesta zahájena.');
    }

    #[Computed]
    public function activeTrips()
    {
        $klicSubjektu = auth()->user()->klic_subjektu;
        if (! $klicSubjektu) {
            return collect();
        }

        return SluzebniCesta::activeForUser($klicSubjektu)
            ->with(['doklad', 'operace', 'zakaznikSubjekt', 'pracovisteSubjekt'])
            ->get();
    }

    #[Computed]
    public function selectedTrip(): ?SluzebniCesta
    {
        if (! $this->selectedTripKey) {
            return null;
        }

        return SluzebniCesta::where('KlicSluzebniCesty', $this->selectedTripKey)
            ->with(['doklad', 'operace', 'zakaznikSubjekt', 'pracovisteSubjekt'])
            ->first();
    }
}
