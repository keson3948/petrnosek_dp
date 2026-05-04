<?php

namespace App\Livewire\Terminal;

use App\Models\ProductionRecord;
use App\Models\Terminal;
use App\Models\User;
use Livewire\Component;

class WorkplaceOverview extends Component
{
    public function render()
    {
        $terminal = Terminal::current();
        $klicPracoviste = trim($terminal?->klic_pracoviste ?? '');

        if ($klicPracoviste === '') {
            return view('livewire.terminal.workplace-overview', [
                'noWorkplace' => true,
                'rows' => collect(),
                'pracovisteName' => null,
            ]);
        }

        $records = ProductionRecord::work()
            ->whereIn('status', [0, 1])
            ->where('pracoviste_id', $klicPracoviste)
            ->with(['machine', 'operation', 'doklad', 'podsestav'])
            ->get();

        $userKeys = $records->pluck('user_id')
            ->map(fn ($k) => trim($k))
            ->filter()
            ->unique();

        $users = $userKeys->isEmpty()
            ? collect()
            : User::whereIn('klic_subjektu', $userKeys)->get()->keyBy('klic_subjektu');

        $mistrKeys = $records->map(fn ($r) => trim($r->doklad?->VlastniOsoba ?? ''))->filter()->unique();
        $mistri = $mistrKeys->isEmpty()
            ? collect()
            : User::whereIn('klic_subjektu', $mistrKeys)->get()->keyBy('klic_subjektu');

        $rows = $records->map(function ($r) use ($users, $mistri) {
            $userModel = $users[trim($r->user_id)] ?? null;
            $pods = $r->podsestav;
            $mistrKey = trim($r->doklad?->VlastniOsoba ?? '');
            $mistr = $mistrKey ? ($mistri[$mistrKey] ?? null) : null;

            return (object) [
                'operator'       => $userModel?->name ?? '—',
                'machine'        => trim($r->machine?->NazevUplny ?? $r->machine_id ?? '') ?: '—',
                'operation'      => trim($r->operation?->Nazev1 ?? $r->operation_id ?? '') ?: '—',
                'vp'             => trim(($r->doklad?->MPSProjekt ?? '').' '.($r->doklad?->KlicDokla ?? '')) ?: null,
                'radek_pozice'   => trim($r->ZakVP_pozice_radku ?? '') ?: null,
                'podsestava'     => $pods ? trim($pods->OznaceniPodsestavy ?? '').'/'.(trim($pods->CisloPoziceNaVykresu ?? '') ?: '-') : null,
                'drawing_number' => trim($r->drawing_number ?? '') ?: null,
                'is_paused'      => $r->status === 1,
                'mistr_cislo'    => $mistr?->cislo_mistra ?? null,
                'mistr_color'    => $mistr?->color ?? '#9ca3af',
            ];
        })->values();

        return view('livewire.terminal.workplace-overview', [
            'noWorkplace' => false,
            'rows' => $rows,
            'pracovisteName' => trim($terminal->pracoviste?->NazevUplny ?? '') ?: null,
        ]);
    }
}
