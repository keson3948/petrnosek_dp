<?php

namespace App\Livewire\Dashboard\Concerns;

use App\Models\Doklad;
use App\Models\EvPodsestav;
use App\Models\PrednOperProstr;
use App\Models\PrednOsobProstr;
use App\Models\Prostredek;
use App\Models\Terminal;
use Livewire\Attributes\Computed;

trait ProvidesWizardData
{
    #[Computed]
    public function selectedDoklad(): ?Doklad
    {
        if (! $this->selectedSysPrimKlic) {
            return null;
        }

        return Doklad::allTypes()
            ->with(['radky.materialPolozka', 'radky.evPodsestavy'])
            ->where('SysPrimKlicDokladu', $this->selectedSysPrimKlic)
            ->first();
    }

    #[Computed]
    public function evPodsestav(): ?EvPodsestav
    {
        return $this->evPodsestavId ? EvPodsestav::find($this->evPodsestavId) : null;
    }

    #[Computed]
    public function podSearchResults()
    {
        if (mb_strlen(trim($this->podSearch)) < 2) {
            return collect();
        }

        $term = mb_substr(mb_strtoupper(trim($this->podSearch)), 0, 10);

        return Doklad::allTypes()
            ->searchByTerm($term)
            ->orderBy('KlicDokla')
            ->limit(30)
            ->get();
    }

    #[Computed]
    public function selectedDokladRadky()
    {
        $doklad = $this->selectedDoklad;

        if (! $doklad) {
            return collect();
        }

        $radky = $doklad->radky;

        if ($this->radekFilter) {
            $filter = mb_strtolower(trim($this->radekFilter));
            $radky = $radky->filter(fn ($r) => str_contains(mb_strtolower(trim($r->CisloRadk ?? '')), $filter)
                || str_contains(mb_strtolower(trim($r->Pozice ?? '')), $filter)
                || str_contains(mb_strtolower(trim($r->materialPolozka?->Nazev1 ?? '')), $filter)
            );
        }

        return $radky->values();
    }

    #[Computed]
    public function selectedRadekPodsestavy()
    {
        if (! $this->selectedSysPrimKlic || ! $this->selectedDokladRadekEntita) {
            return collect();
        }

        $podsestavy = EvPodsestav::where('EntitaRadkuVP', $this->selectedDokladRadekEntita)->get();

        if ($this->podsFilter) {
            $filter = mb_strtolower(trim($this->podsFilter));
            $podsestavy = $podsestavy->filter(fn ($p) => str_contains(mb_strtolower(trim($p->OznaceniPodsestavy ?? '')), $filter)
                || str_contains(mb_strtolower(trim($p->Pozice ?? '')), $filter)
                || str_contains(mb_strtolower(trim($p->CisloVykresu ?? '')), $filter)
            );
        }

        return $podsestavy->values();
    }

    #[Computed]
    public function startMachineOperations()
    {
        if (! $this->machine_id) {
            return collect();
        }

        $assigned = PrednOperProstr::forProstredek($this->machine_id)
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

    #[Computed]
    public function userMachines()
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
}
