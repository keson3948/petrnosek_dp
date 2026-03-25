<?php

namespace App\Livewire\Zasobovac;

use App\Models\Doklad;
use App\Models\StaDokl;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Mary\Traits\Toast;

#[Layout('layouts.app')]
class Index extends Component
{
    use Toast;

    public string $search = '';

    public function boot()
    {
        abort_if(! auth()->user()->can('manage zasobovani'), 403);
    }

    public function headers(): array
    {
        return [
            ['key' => 'klic_dokla', 'label' => 'Klíč Dokladu'],
            ['key' => 'mps_projekt', 'label' => 'MPS Projekt'],
            ['key' => 'vlastni_osoba', 'label' => 'Vlastní Osoba'],
            ['key' => 'zakazka', 'label' => 'Zakázka'],
            ['key' => 'specificky_symbol', 'label' => 'Spec. Symbol'],
            ['key' => 'termin_datum', 'label' => 'Termín'],
            ['key' => 'doklad_id', 'label' => 'ID', 'hidden' => true],
        ];
    }

    public function render()
    {
        $query = StaDokl::with(['doklad.vlastniOsoba', 'doklad.rodicZakazka'])
            ->typPohybu('EC_ZAKVYR')
            ->vyhodnoceni(1)
            ->whereHas('doklad', function (Builder $q) {
                $q->tdfDocType(410008)
                    ->dbcnt(10904)
                    ->docYear(2025);

                if ($this->search) {
                    $q->where(function (Builder $sq) {
                        $sq->where('KlicDokla', 'like', "%{$this->search}%")
                            ->orWhere('MPSProjekt', 'like', "%{$this->search}%");
                    });
                }
            });

        $staDoklady = $query
            ->orderBy(
                Doklad::select('TerminDatum')
                    ->whereColumn('ecd_Dokl.SysPrimKlicDokladu', 'ecd_StaDokl.Doklad'),
                'asc'
            )
            ->get();

        $staDoklady->transform(function ($staDokl) {
            $staDokl->klic_dokla = trim($staDokl->doklad->KlicDokla ?? '');
            $staDokl->mps_projekt = trim($staDokl->doklad->MPSProjekt ?? '');
            $staDokl->vlastni_osoba = trim($staDokl->doklad->vlastniOsoba->Prijmeni ?? '-');
            $staDokl->zakazka = trim($staDokl->doklad->rodicZakazka->KlicDokla ?? '-');
            $staDokl->specificky_symbol = trim($staDokl->doklad->rodicZakazka->SpecifiSy ?? '-');
            $staDokl->termin_datum = $staDokl->doklad->TerminDatum ?? '-';
            $staDokl->doklad_id = trim($staDokl->Doklad);

            return $staDokl;
        });

        return view('livewire.zasobovac.index', [
            'headers' => $this->headers(),
            'staDoklady' => $staDoklady,
        ]);
    }
}
