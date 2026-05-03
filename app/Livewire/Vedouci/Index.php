<?php

namespace App\Livewire\Vedouci;

use App\Models\Attendance\Pruchod;
use App\Models\ProductionRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    public string $search = '';

    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    public function boot()
    {
        abort_if(! auth()->user()->can('manage production records'), 403);
    }

    #[Computed]
    public function activeRecords()
    {
        return ProductionRecord::work()
            ->whereIn('status', [0, 1])
            ->with(['doklad', 'machine', 'operation'])
            ->get()
            ->keyBy(fn ($r) => trim($r->user_id));
    }

    public function render()
    {
        $presentHeaders = [
            ['key' => 'skupina_label', 'label' => 'Skupina', 'sortable' => false, 'class' => 'w-48 bg-base-200'],
            ['key' => 'name', 'label' => 'Jméno', 'class' => 'w-64'],
            ['key' => 'arrival', 'label' => 'Příchod', 'sortable' => false, 'class' => 'w-1 text-center bg-info/20'],
            ['key' => 'current_vp', 'label' => 'Aktuální VP', 'sortable' => false],
            ['key' => 'current_operation', 'label' => 'Operace', 'sortable' => false],
            ['key' => 'current_machine', 'label' => 'Stroj', 'sortable' => false],
            ['key' => 'started_at_label', 'label' => 'Zahájeno', 'sortable' => false, 'class' => 'w-1 text-center'],
            ['key' => 'lunch_time_label', 'label' => 'Oběd', 'sortable' => false, 'class' => 'w-1 text-center'],
        ];

        $absentHeaders = [
            ['key' => 'skupina_label', 'label' => 'Skupina', 'sortable' => false, 'class' => 'w-48 bg-base-200'],
            ['key' => 'name', 'label' => 'Jméno', 'class' => 'w-64'],
            ['key' => 'arrival', 'label' => 'Příchod', 'sortable' => false, 'class' => 'w-1 bg-success/20 text-center'],
            ['key' => 'departure', 'label' => 'Odchod', 'sortable' => false, 'class' => 'w-1 bg-error/20 text-center'],
            ['key' => 'attendance_date', 'label' => 'Datum', 'sortable' => false, 'class' => 'w-1 text-center'],
            ['key' => 'worked_hours', 'label' => 'Odpracováno', 'sortable' => false, 'class' => 'w-1 text-center'],
            ['key' => 'lunch_time_label', 'label' => 'Oběd', 'sortable' => false, 'class' => 'w-1 text-center'],
            ['key' => '', 'label' => '', 'sortable' => false],
        ];

        $users = User::query()
            ->whereNotNull('klic_subjektu')
            ->where('klic_subjektu', '!=', '')
            ->with('vztah.skupinaZamestnancu')
            ->when($this->search, fn (Builder $q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->get();

        $activeRecords = $this->activeRecords;

        $attendanceMap = $this->loadAttendance($users);

        $allUsers = $users->map(function ($user) use ($activeRecords, $attendanceMap) {
            $active = $activeRecords->get($user->klic_subjektu);
            $skupina = $user->vztah?->skupinaZamestnancu;

            $user->skupina_label = $skupina ? trim($skupina->Nazev ?? '') : '';
            $user->lunch_time_label = $skupina?->lunchCarbon()?->format('H:i') ?? '';

            $user->is_active = (bool) $active;
            $user->current_vp = $active
                ? trim(($active->doklad?->MPSProjekt ?? '').' '.($active->doklad?->KlicDokla ?? ''))
                : '';
            $user->current_vp_sys_klic = $active ? trim($active->ZakVP_SysPrimKlic ?? '') : '';
            $user->current_operation = $active ? trim($active->operation?->Nazev1 ?? $active->operation_id ?? '') : '';
            $user->current_machine = $active ? trim($active->machine?->NazevUplny ?? $active->machine_id ?? '') : '';
            $user->started_at_label = $active?->started_at?->format('H:i') ?? '';

            $att = $attendanceMap->get($user->id);
            $user->arrival = $att['arrival'] ?? null;
            $user->departure = $att['departure'] ?? null;
            $user->is_present = $att['is_present'] ?? false;
            $user->attendance_date = $att['date'] ?? null;

            $minutes = $att['worked_minutes'] ?? 0;
            $user->worked_hours = $minutes > 0
                ? sprintf('%d:%02d', intdiv($minutes, 60), $minutes % 60)
                : null;

            return $user;
        });

        $presentUsers = $allUsers->filter(fn ($u) => $u->is_present)->values();
        $absentUsers = $allUsers->filter(fn ($u) => ! $u->is_present)->values();

        return view('livewire.vedouci.index', [
            'presentUsers' => $presentUsers,
            'absentUsers' => $absentUsers,
            'presentHeaders' => $presentHeaders,
            'absentHeaders' => $absentHeaders,
        ]);
    }


    private function loadAttendance(Collection $users): Collection
    {
        try {
            $oscToUserId = $users
                ->filter(fn ($u) => $u->osobni_cislo_dochazky)
                ->mapWithKeys(fn ($u) => [$u->osobni_cislo_dochazky => $u->id]);

            if ($oscToUserId->isEmpty()) {
                return collect();
            }

            $pruchody = Pruchod::vceraADnes()
                ->whereIn('OSC', $oscToUserId->keys()->all())
                ->orderBy('DATUM')
                ->orderBy('CAS')
                ->get();

            return $pruchody->groupBy('OSC')->mapWithKeys(function ($group) use ($oscToUserId) {
                $userId = $oscToUserId->get((string) $group->first()->OSC)
                    ?? $oscToUserId->get((int) $group->first()->OSC);

                $lastArrival = $group->last(fn ($p) => (int) $p->DIRECTION === 1);
                $lastDeparture = $group->last(fn ($p) => (int) $p->DIRECTION !== 1);

                $isPresent = (int) $group->last()->DIRECTION === 1;

                $showDeparture = $lastDeparture && $lastArrival
                    && ($lastDeparture->DATUM > $lastArrival->DATUM
                        || ($lastDeparture->DATUM === $lastArrival->DATUM && $lastDeparture->CAS > $lastArrival->CAS));

                $workedMinutes = 0;
                if ($lastArrival && $showDeparture) {
                    $arrMin = (int) $lastArrival->DATUM * 1440 + (int) $lastArrival->CAS;
                    $depMin = (int) $lastDeparture->DATUM * 1440 + (int) $lastDeparture->CAS;
                    $workedMinutes = max(0, $depMin - $arrMin);
                }

                return [$userId => [
                    'arrival' => $lastArrival?->cas_time,
                    'departure' => $showDeparture ? $lastDeparture->cas_time : null,
                    'is_present' => $isPresent,
                    'worked_minutes' => $workedMinutes,
                    'date' => $lastArrival?->datum_date?->format('d.m.'),
                ]];
            });
        } catch (\Exception $e) {
            return collect();
        }
    }
}
