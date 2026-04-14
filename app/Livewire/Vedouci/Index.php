<?php

namespace App\Livewire\Vedouci;

use App\Models\Attendance\Osoba as AttendanceOsoba;
use App\Models\Attendance\Pruchod;
use App\Models\ProductionRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    public function boot()
    {
        abort_if(! auth()->user()->can('manage production records'), 403);
    }

    #[Computed]
    public function activeRecords()
    {
        return ProductionRecord::whereIn('status', [0, 1])
            ->with(['doklad', 'machine', 'operation'])
            ->get()
            ->keyBy(fn ($r) => trim($r->user_id));
    }

    public function render()
    {
        $headers = [
            ['key' => 'name', 'label' => 'Jméno'],
            ['key' => 'attendance', 'label' => 'Docházka', 'sortable' => false],
            ['key' => 'status_label', 'label' => 'Stav', 'sortable' => false],
            ['key' => 'current_vp', 'label' => 'Aktuální VP', 'sortable' => false],
            ['key' => 'current_operation', 'label' => 'Operace', 'sortable' => false],
            ['key' => 'current_machine', 'label' => 'Stroj', 'sortable' => false],
            ['key' => 'started_at_label', 'label' => 'Zahájeno', 'sortable' => false],
        ];

        $users = User::query()
            ->whereNotNull('klic_subjektu')
            ->where('klic_subjektu', '!=', '')
            ->when($this->search, fn (Builder $q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate(20);

        $activeRecords = $this->activeRecords;

        $attendanceMap = collect();
        try {
            $cipToUserId = $users->getCollection()
                ->filter(fn ($u) => $u->izo)
                ->mapWithKeys(fn ($u) => [$u->attendance_cip => $u->id]);

            if ($cipToUserId->isNotEmpty()) {
                $osoby = AttendanceOsoba::whereIn('CIP', $cipToUserId->keys()->all())->get();
                $oscToUserId = $osoby->mapWithKeys(fn ($o) => [
                    (int) $o->OSC => $cipToUserId->get(trim($o->CIP)),
                ]);

                if ($oscToUserId->isNotEmpty()) {
                    $pruchody = Pruchod::dnesni()
                        ->whereIn('OSC', $oscToUserId->keys()->all())
                        ->orderBy('CAS')
                        ->get();

                    $attendanceMap = $pruchody->groupBy('OSC')->mapWithKeys(function ($group) use ($oscToUserId) {
                        $last = $group->last();
                        $userId = $oscToUserId->get((int) $last->OSC);

                        return [$userId => [
                            'time' => $last->cas_time,
                            'type' => (int) $last->DIRECTION === 1 ? 'prichod' : 'odchod',
                        ]];
                    });
                }
            }
        } catch (\Exception $e) {
            // MSSQL may not be available
        }

        $users->getCollection()->transform(function ($user) use ($activeRecords, $attendanceMap) {
            $active = $activeRecords->get(trim($user->klic_subjektu));

            $user->is_active = (bool) $active;
            $user->status_label = $active
                ? ($active->status === 0 ? 'Pracuje' : 'Pauza')
                : 'Neaktivní';
            $user->current_vp = $active ? trim(($active->doklad?->MPSProjekt ?? '').' '.($active->doklad?->KlicDokla ?? '')) : '';
            $user->current_vp_sys_klic = $active ? trim($active->ZakVP_SysPrimKlic ?? '') : '';
            $user->current_operation = $active ? trim($active->operation?->Nazev1 ?? $active->operation_id ?? '') : '';
            $user->current_machine = $active ? trim($active->machine?->NazevUplny ?? $active->machine_id ?? '') : '';
            $user->started_at_label = $active?->started_at?->format('H:i') ?? '';

            // Docházka
            $att = $attendanceMap->get($user->id);
            $user->attendance_time = $att['time'] ?? null;
            $user->attendance_type = $att['type'] ?? null;

            return $user;
        });

        return view('livewire.vedouci.index', [
            'users' => $users,
            'headers' => $headers,
        ]);
    }
}
