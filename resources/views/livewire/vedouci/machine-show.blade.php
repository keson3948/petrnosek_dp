<div>
    <x-mary-header :title="$machineName" separator>
        <x-slot:subtitle>{{ $machineKey }}</x-slot:subtitle>
        <x-slot:actions>
            <x-mary-button label="Zpět" icon="o-arrow-left" link="{{ route('vedouci.machines') }}" />
        </x-slot:actions>
    </x-mary-header>

    {{-- Aktivní operace --}}
    @if($this->activeRecord)
        @php $active = $this->activeRecord; @endphp
        <x-mary-card title="Aktivní operace" class="mb-6 border-2 {{ $active->status == 1 ? 'border-warning' : 'border-success' }}">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <div class="text-xs text-gray-400 uppercase">Operátor</div>
                    <div class="font-semibold text-lg">{{ $this->userNames[trim($active->user_id)] ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 uppercase">Stav</div>
                    <x-mary-badge :value="$active->status == 1 ? 'Pauza' : 'Pracuje'"
                        class="{{ $active->status == 1 ? 'badge-warning' : 'badge-success' }}" />
                </div>
                <div>
                    <div class="text-xs text-gray-400 uppercase">VP</div>
                    <div class="font-mono font-semibold">{{ trim($active->doklad?->KlicDokla ?? '') ?: '—' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 uppercase">Operace</div>
                    <div class="font-semibold">{{ trim($active->operation?->Nazev1 ?? $active->operation_id ?? '') ?: '—' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 uppercase">Zahájeno</div>
                    <div class="tabular-nums">{{ $active->started_at?->format('d.m.Y H:i') ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 uppercase">Výkres</div>
                    <div>{{ $active->drawing_number ?: '—' }}</div>
                </div>
            </div>
        </x-mary-card>
    @endif

    {{-- Přiřazení operátoři --}}
    @if($this->assignedUsers->isNotEmpty())
        <x-mary-card title="Přiřazení operátoři" class="mb-6">
            <div class="flex flex-wrap gap-2">
                @foreach($this->assignedUsers as $user)
                    <a href="{{ route('vedouci.show', $user->klic_subjektu) }}" class="badge badge-lg badge-outline gap-2 hover:badge-primary transition-colors">
                        {{ $user->name }}
                    </a>
                @endforeach
            </div>
        </x-mary-card>
    @endif

    {{-- Historie --}}
    <div class="flex flex-wrap items-end gap-4 mb-6">
        <x-mary-input label="Od" type="date" wire:model.live="dateFrom" class="w-40" />
        <x-mary-input label="Do" type="date" wire:model.live="dateTo" class="w-40" />
    </div>

    <x-mary-card>
        <x-mary-table :headers="$headers" :rows="$this->records" striped>

            @scope('cell_started_at', $record)
                <span class="whitespace-nowrap tabular-nums">{{ $record->started_at?->format('d.m.Y') ?? '—' }}</span>
            @endscope

            @scope('cell_operator', $record)
                <span class="font-semibold">{{ $this->userNames[trim($record->user_id)] ?? '—' }}</span>
            @endscope

            @scope('cell_vp', $record)
                <span class="font-mono">{{ trim($record->doklad?->KlicDokla ?? '') ?: '—' }}</span>
            @endscope

            @scope('cell_operation', $record)
                {{ trim($record->operation?->Nazev1 ?? $record->operation_id ?? '') ?: '—' }}
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
