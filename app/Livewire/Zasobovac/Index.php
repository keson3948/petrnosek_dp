<?php

namespace App\Livewire\Zasobovac;

use App\Models\Doklad;
use App\Models\StaDokl;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

#[Layout('layouts.app')]
class Index extends Component
{
    use Toast, WithPagination;

    public string $search = '';

    public array $sortBy = ['column' => 'termin_datum', 'direction' => 'asc'];

    public ?string $filterMistr = null;

    public array $mistrOptions = [];

    public function boot()
    {
        abort_if(! auth()->user()->can('manage zasobovani'), 403);
    }

    public function headers(): array
    {
        return [
            ['key' => 'klic_dokla', 'label' => 'Klíč Dokladu'],
            ['key' => 'mps_projekt', 'label' => 'MPS Projekt'],
            ['key' => 'vlastni_osoba', 'label' => 'Mistr'],
            ['key' => 'garant', 'label' => 'Garant'],
            ['key' => 'zakazka', 'label' => 'Zakázka'],
            ['key' => 'specificky_symbol', 'label' => 'Int. projekt'],
            ['key' => 'termin_datum', 'label' => 'Termín'],
            ['key' => 'doklad_id', 'label' => 'ID', 'hidden' => true],
        ];
    }

    public function render()
    {
        $staDoklady = $this->loadStaDoklady();

        if (empty($this->mistrOptions)) {
            $this->mistrOptions = $staDoklady
                ->map(fn ($s) => [
                    'id' => trim($s->doklad->vlastniOsoba?->KlicSubjektu ?? ''),
                    'name' => trim($s->doklad->vlastniOsoba?->Prijmeni ?? ''),
                ])
                ->filter(fn ($m) => $m['id'] !== '' && $m['name'] !== '')
                ->unique('id')
                ->sortBy('name')
                ->values()
                ->toArray();
        }

        $mistrUsers = $this->loadMistrUsers($staDoklady);
        $rows = $this->transformRows($staDoklady, $mistrUsers);

        if ($this->search) {
            $term = mb_strtolower(trim($this->search));
            $rows = $rows->filter(fn ($row) => str_contains(mb_strtolower($row->klic_dokla ?? ''), $term)
                || str_contains(mb_strtolower($row->mps_projekt ?? ''), $term)
                || str_contains(mb_strtolower($row->vlastni_osoba ?? ''), $term)
                || str_contains(mb_strtolower($row->garant ?? ''), $term)
                || str_contains(mb_strtolower($row->zakazka ?? ''), $term)
                || str_contains(mb_strtolower($row->specificky_symbol ?? ''), $term)
            )->values();
        }

        $perPage = 15;
        $page = $this->getPage('page');

        $paginator = new LengthAwarePaginator(
            $rows->forPage($page, $perPage),
            $rows->count(),
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'pageName' => 'page']
        );

        return view('livewire.zasobovac.index', [
            'headers' => $this->headers(),
            'staDoklady' => $paginator,
        ]);
    }

    protected function loadStaDoklady(): Collection
    {
        return StaDokl::with([
            'doklad' => fn ($q) => $q->select([
                'SysPrimKlicDokladu', 'KlicDokla', 'MPSProjekt',
                'VlastniOsoba', 'Zakazka', 'TerminDatum',
            ]),
            'doklad.vlastniOsoba' => fn ($q) => $q->select(['KlicSubjektu', 'Prijmeni']),
            'doklad.rodicZakazka' => fn ($q) => $q->select([
                'SysPrimKlicDokladu', 'KlicDokla', 'SpecifiSy', 'VlastniOsoba',
            ]),
            'doklad.rodicZakazka.vlastniOsoba' => fn ($q) => $q->select(['KlicSubjektu', 'Prijmeni']),
        ])
            ->typPohybu('EC_ZAKVYR')
            ->vyhodnoceni(1)
            ->whereHas('doklad', function (Builder $q) {
                $q->tdfDocType(410008)
                    ->dbcnt(10904)
                    ->docYear(2022);

                if ($this->filterMistr) {
                    $q->where('VlastniOsoba', $this->filterMistr);
                }

            })
            ->orderBy(
                Doklad::select('TerminDatum')
                    ->whereColumn('ecd_Dokl.SysPrimKlicDokladu', 'ecd_StaDokl.Doklad'),
                'asc'
            )
            ->get();
    }

    protected function loadMistrUsers(Collection $staDoklady): Collection
    {
        $kliceSubjektu = $staDoklady
            ->map(fn ($s) => trim($s->doklad->vlastniOsoba?->KlicSubjektu ?? ''))
            ->filter()
            ->unique();

        return User::whereIn('klic_subjektu', $kliceSubjektu)->get()->keyBy('klic_subjektu');
    }

    protected function transformRows(Collection $staDoklady, Collection $mistrUsers): Collection
    {
        $staDoklady->transform(function ($staDokl) use ($mistrUsers) {
            $key = trim($staDokl->doklad->vlastniOsoba?->KlicSubjektu ?? '');
            $mistrUser = $mistrUsers[$key] ?? null;

            $staDokl->klic_dokla = trim($staDokl->doklad->KlicDokla ?? '');
            $staDokl->mps_projekt = trim($staDokl->doklad->MPSProjekt ?? '');
            $staDokl->vlastni_osoba = trim($staDokl->doklad->vlastniOsoba->Prijmeni ?? '-');
            $staDokl->mistr_color = $mistrUser?->color;
            $staDokl->mistr_cislo = $mistrUser?->cislo_mistra;
            $staDokl->garant = trim($staDokl->doklad->rodicZakazka?->vlastniOsoba?->Prijmeni ?? '-');
            $staDokl->zakazka = trim($staDokl->doklad->rodicZakazka->KlicDokla ?? '-');
            $staDokl->specificky_symbol = trim($staDokl->doklad->rodicZakazka->SpecifiSy ?? '-');
            $staDokl->termin_datum = $staDokl->doklad->TerminDatum ?? '-';
            $staDokl->doklad_id = trim($staDokl->Doklad);

            return $staDokl;
        });

        return $staDoklady->sortBy(
            $this->sortBy['column'],
            SORT_NATURAL | SORT_FLAG_CASE,
            $this->sortBy['direction'] === 'desc'
        )->values();
    }
}
