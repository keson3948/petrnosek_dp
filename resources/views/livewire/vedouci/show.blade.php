<div>
    <x-mary-header :title="$operator->name" separator>
        <x-slot:actions>
            <x-mary-button label="Přidat záznam" icon="o-plus" class="btn-primary" link="{{ route('vedouci.record-edit', $operator->klic_subjektu) }}" />
        </x-slot:actions>
    </x-mary-header>

    {{-- Aktivní operace --}}
    @if($this->activeRecord)
        @php $active = $this->activeRecord; @endphp
        <x-mary-card title="Aktuálně pracuje" class="mb-6 border-2 {{ $active->status == 1 ? 'border-warning' : 'border-success' }}">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <div class="text-xs text-gray-400 uppercase">Stav</div>
                    <x-mary-badge :value="$active->status == 1 ? 'Pauza' : 'Pracuje'"
                        class="{{ $active->status == 1 ? 'badge-warning' : 'badge-success' }}" />
                </div>
                <div>
                    <div class="text-xs text-gray-400 uppercase">Stroj</div>
                    <div class="font-semibold">{{ trim($active->machine?->NazevUplny ?? $active->machine_id ?? '') ?: '—' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 uppercase">VP</div>
                    <div class="font-mono font-semibold">
                        @if($active->ZakVP_SysPrimKlic)
                            <a href="{{ route('vp.show', trim($active->ZakVP_SysPrimKlic)) }}" class="hover:underline hover:text-primary">{{ trim($active->doklad?->MPSProjekt ?? '') }} {{ trim($active->doklad?->KlicDokla ?? '') ?: '—' }}</a>
                        @else
                            —
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 uppercase">Operace</div>
                    <div class="font-semibold">{{ trim($active->operation?->Nazev1 ?? $active->operation_id ?? '') ?: '—' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 uppercase">Zahájeno</div>
                    <div class="tabular-nums">{{ $active->started_at?->format('d.m.Y H:i') ?? '—' }}</div>
                </div>
            </div>
        </x-mary-card>
    @endif

    {{-- Filters --}}
    <div class="flex flex-wrap justify-end gap-4 mb-6 w-full">
        <x-mary-input label="Od" type="date" wire:model.live="dateFrom" class="w-40" />
        <x-mary-input label="Do" type="date" wire:model.live="dateTo" class="w-40" />
    </div>

    {{-- Records Table --}}
    <x-mary-card>
        <x-mary-table :headers="$headers" :rows="$this->records" striped>

            @scope('cell_started_at', $record)
                <span class="whitespace-nowrap tabular-nums">{{ $record->started_at?->format('d.m.Y H:i') ?? '—' }}</span>
            @endscope

            @scope('cell_ended_at', $record)
                <span class="whitespace-nowrap tabular-nums">{{ $record->ended_at?->format('d.m.Y H:i') ?? '—' }}</span>
            @endscope

            @scope('cell_vp', $record)
                @if($record->ZakVP_SysPrimKlic)
                    <a href="{{ route('vp.show', trim($record->ZakVP_SysPrimKlic)) }}" class="font-mono hover:underline hover:text-primary">{{ trim($record->doklad?->MPSProjekt ?? '') }} {{ trim($record->doklad?->KlicDokla ?? '') ?: '—' }}</a>
                @else
                    <span class="font-mono">—</span>
                @endif
            @endscope

            @scope('cell_machine', $record)
                {{ trim($record->machine?->NazevUplny ?? $record->machine_id ?? '') ?: '—' }}
            @endscope

            @scope('cell_operation', $record)
                {{ trim($record->operation?->Nazev1 ?? $record->operation_id) }}
            @endscope

            @scope('cell_quantity', $record)
                {{ $record->processed_quantity ?? 0 }} ks
            @endscope

            @scope('cell_time', $record)
                @php
                    $workedH = null;
                    $workedM = null;
                    if ($record->started_at && $record->ended_at) {
                        $totalMinutes = max(0, intval($record->started_at->diffInMinutes($record->ended_at)) - ($record->total_paused_min ?? 0));
                        $workedH = intdiv($totalMinutes, 60);
                        $workedM = $totalMinutes % 60;
                    }
                @endphp
                <span class="whitespace-nowrap tabular-nums">
                    @if($workedH !== null)
                        {{ $workedH }}:{{ str_pad($workedM, 2, '0', STR_PAD_LEFT) }}
                    @else
                        —
                    @endif
                </span>
            @endscope

            @scope('cell_notes', $record)
                <span class="max-w-48 truncate block">{{ $record->notes ?: '—' }}</span>
            @endscope

            @scope('cell_actions', $record)
                <div class="text-right whitespace-nowrap">
                    <x-mary-button icon="o-pencil" link="{{ route('vedouci.record-edit', [$this->operator->klic_subjektu, $record->ID]) }}" class="btn-ghost btn-sm" />
                    <x-mary-button icon="o-trash" wire:click="deleteRecord({{ $record->ID }})" wire:confirm="Opravdu smazat tento záznam?" class="btn-ghost btn-sm text-error" spinner />
                </div>
            @endscope

        </x-mary-table>

        <div class="mt-4 text-sm text-gray-500">
            Celkem {{ $this->records->count() }} záznamů
            @php
                $totalMin = $this->records->sum(function ($r) {
                    if ($r->started_at && $r->ended_at) {
                        return max(0, intval($r->started_at->diffInMinutes($r->ended_at)) - ($r->total_paused_min ?? 0));
                    }
                    return 0;
                });
            @endphp
            | Celkem odpracováno: {{ intdiv($totalMin, 60) }}h {{ $totalMin % 60 }}min
        </div>
    </x-mary-card>

</div>
