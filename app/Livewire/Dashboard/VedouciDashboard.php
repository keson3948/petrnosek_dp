<?php

namespace App\Livewire\Dashboard;

use App\Models\Doklad;
use App\Models\Pracoviste;
use App\Models\ProductionRecord;
use App\Models\StaDokl;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class VedouciDashboard extends Component
{
    use WithPagination;

    public string $activeTab = 'all';

    public ?string $filterMistr = null;

    public string $search = '';

    #[Computed]
    public function activeRecords(): Collection
    {
        return ProductionRecord::whereIn('status', [0, 1])
            ->with(['doklad.vlastniOsoba', 'machine.pracoviste', 'operation'])
            ->get();
    }

    #[Computed]
    public function activeUsers(): Collection
    {
        $userKeys = $this->activeRecords
            ->pluck('user_id')
            ->map(fn ($k) => trim($k))
            ->filter()
            ->unique();

        if ($userKeys->isEmpty()) {
            return collect();
        }

        return User::whereIn('klic_subjektu', $userKeys)->get()->keyBy('klic_subjektu');
    }

    #[Computed]
    public function halls(): Collection
    {
        return Pracoviste::all()
            ->map(function ($p) {
                $name = trim($p->NazevUplny ?? '');
                if (preg_match('/^(H\d+)/', $name, $m)) {
                    return $m[1];
                }
                return null;
            })
            ->filter()
            ->unique()
            ->sort()
            ->values();
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
            ->merge(
                $this->activeRecords->map(fn ($r) => trim($r->doklad?->vlastniOsoba?->KlicSubjektu ?? ''))
            )
            ->filter()
            ->unique();

        if ($kliceSubjektu->isEmpty()) {
            return collect();
        }

        return User::whereIn('klic_subjektu', $kliceSubjektu)->get()->keyBy('klic_subjektu');
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function updatedFilterMistr(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $staDoklady = $this->loadStaDoklady();
        $mistrUsers = $this->loadMistrUsers($staDoklady);
        $activeUserModels = $this->activeUsers;

        // Build mistr filter options
        $mistrOptions = $staDoklady
            ->map(fn ($s) => [
                'id' => trim($s->doklad->vlastniOsoba?->KlicSubjektu ?? ''),
                'name' => trim($s->doklad->vlastniOsoba?->Prijmeni ?? ''),
            ])
            ->filter(fn ($m) => $m['id'] !== '' && $m['name'] !== '')
            ->unique('id')
            ->sortBy('name')
            ->values()
            ->toArray();

        // Map active records by VP SysPrimKlic for quick lookup
        $activeByVp = $this->activeRecords->groupBy(fn ($r) => trim($r->ZakVP_SysPrimKlic ?? ''));

        // ===== ACTIVE ROWS =====
        $activeRows = $this->activeRecords->map(function ($r) use ($activeUserModels, $mistrUsers) {
            $userModel = $activeUserModels[trim($r->user_id)] ?? null;
            $mistrKey = trim($r->doklad?->vlastniOsoba?->KlicSubjektu ?? '');
            $mistrUser = $mistrUsers[$mistrKey] ?? null;

            $pracovisteName = trim($r->machine?->pracoviste?->NazevUplny ?? '');
            $hall = '';
            if (preg_match('/^(H\d+)/', $pracovisteName, $m)) {
                $hall = $m[1];
            }

            $r->_operator_name = $userModel?->name ?? '—';
            $r->_mistr_color = $mistrUser?->color ?? '#6b7280';
            $r->_mistr_cislo = $mistrUser?->cislo_mistra ?? '';
            $r->_machine_name = trim($r->machine?->NazevUplny ?? $r->machine_id ?? '') ?: '—';
            $r->_machine_with_hall = $pracovisteName ? $pracovisteName : $r->_machine_name;
            $r->_hall = $hall;
            $r->_operation_name = trim($r->operation?->Nazev1 ?? $r->operation_id ?? '') ?: '—';
            $r->_vp_label = trim(($r->doklad?->MPSProjekt ?? '') . ' ' . ($r->doklad?->KlicDokla ?? '')) ?: '—';
            $r->_vp_sys_klic = trim($r->ZakVP_SysPrimKlic ?? '');
            $r->_spec_symbol = trim($r->doklad?->rodicZakazka?->SpecifiSy ?? '') ?: '—';
            $r->_termin = $r->doklad?->TerminDatum ?? null;
            $r->_user_klic = trim($r->user_id ?? '');
            $r->_machine_key = trim($r->machine_id ?? '');
            $r->_machine_exists = (bool) $r->machine;

            return $r;
        });

        // Filter by hall tab
        if ($this->activeTab !== 'all') {
            $filteredActive = $activeRows->filter(fn ($r) => $r->_hall === $this->activeTab)->values();
        } else {
            $filteredActive = $activeRows;
        }

        // Sort by hall, then machine
        $filteredActive = $filteredActive->sortBy([
            ['_hall', 'asc'],
            ['_machine_with_hall', 'asc'],
        ])->values();

        // Collect VP keys that are active (to exclude from rozpracované)
        $activeVpKeys = $this->activeRecords->pluck('ZakVP_SysPrimKlic')
            ->map(fn ($k) => trim($k))
            ->filter()
            ->unique();

        // ===== VP ROWS =====
        $vpRows = $staDoklady->map(function ($staDokl) use ($mistrUsers) {
            $key = trim($staDokl->doklad->vlastniOsoba?->KlicSubjektu ?? '');
            $mistrUser = $mistrUsers[$key] ?? null;

            $staDokl->klic_dokla = trim($staDokl->doklad->KlicDokla ?? '');
            $staDokl->mps_projekt = trim($staDokl->doklad->MPSProjekt ?? '');
            $staDokl->vlastni_osoba = trim($staDokl->doklad->vlastniOsoba->Prijmeni ?? '-');
            $staDokl->mistr_color = $mistrUser?->color;
            $staDokl->mistr_cislo = $mistrUser?->cislo_mistra;
            $staDokl->garant = trim($staDokl->doklad->rodicZakazka?->vlastniOsoba?->Prijmeni ?? '-');
            $staDokl->specificky_symbol = trim($staDokl->doklad->rodicZakazka->SpecifiSy ?? '-');
            $staDokl->termin_datum = $staDokl->doklad->TerminDatum ?? '-';
            $staDokl->doklad_id = trim($staDokl->Doklad);

            return $staDokl;
        });

        // Exclude VPs that are currently active
        $vpRows = $vpRows->filter(fn ($row) => !$activeVpKeys->contains($row->doklad_id));

        // Search filter
        if (!empty($this->search)) {
            $term = mb_strtolower(trim($this->search));
            $vpRows = $vpRows->filter(function ($row) use ($term) {
                return str_contains(mb_strtolower($row->klic_dokla ?? ''), $term)
                    || str_contains(mb_strtolower($row->mps_projekt ?? ''), $term)
                    || str_contains(mb_strtolower($row->vlastni_osoba ?? ''), $term)
                    || str_contains(mb_strtolower($row->garant ?? ''), $term)
                    || str_contains(mb_strtolower($row->specificky_symbol ?? ''), $term);
            });
        }

        $vpRows = $vpRows->values();

        // Paginate
        $perPage = 20;
        $page = $this->getPage('page');
        $vpPaginator = new LengthAwarePaginator(
            $vpRows->forPage($page, $perPage),
            $vpRows->count(),
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'pageName' => 'page']
        );

        // Headers
        $activeHeaders = [
            ['key' => 'mistr', 'label' => '', 'class' => 'w-12'],
            ['key' => 'vp', 'label' => 'VP'],
            ['key' => 'symbol', 'label' => 'Int. projekt'],
            ['key' => 'termin', 'label' => 'Termín'],
            ['key' => 'operator', 'label' => 'Operátor'],
            ['key' => 'machine', 'label' => 'Stroj'],
            ['key' => 'operation', 'label' => 'Operace'],
            ['key' => 'time', 'label' => 'Čas'],
        ];

        $vpHeaders = [
            ['key' => 'mistr_avatar', 'label' => '', 'class' => 'w-12'],
            ['key' => 'vp_name', 'label' => 'VP'],
            ['key' => 'specificky_symbol', 'label' => 'Int. projekt'],
            ['key' => 'termin', 'label' => 'Termín'],
        ];

        return view('livewire.dashboard.vedouci-dashboard', [
            'activeHeaders' => $activeHeaders,
            'activeRows' => $filteredActive,
            'vpHeaders' => $vpHeaders,
            'vpRows' => $vpPaginator,
            'mistrOptions' => $mistrOptions,
            'hallTabs' => $this->halls,
        ]);
    }
}
