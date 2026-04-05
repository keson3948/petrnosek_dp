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
    <x-mary-card title="Historie záznamů">
        <div class="flex flex-wrap items-end gap-4 mb-4">
            <x-mary-input label="Od" type="date" wire:model.live="dateFrom" class="w-40" />
            <x-mary-input label="Do" type="date" wire:model.live="dateTo" class="w-40" />
        </div>

        @if($this->records->isEmpty())
            <div class="text-center py-8 text-gray-500">Žádné záznamy v tomto období.</div>
        @else
            <div class="overflow-x-auto">
                <table class="table table-sm w-full">
                    <thead>
                        <tr>
                            <th>Datum</th>
                            <th>Operátor</th>
                            <th>VP</th>
                            <th>Operace</th>
                            <th>Množství</th>
                            <th>Čas</th>
                            <th>Poznámka</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($this->records as $record)
                            @php $info = $this->getRecordInfo($record); @endphp
                            <tr class="hover">
                                <td class="whitespace-nowrap">{{ $record->started_at?->format('d.m.Y') }}</td>
                                <td class="font-semibold">{{ $this->userNames[trim($record->user_id)] ?? '—' }}</td>
                                <td class="font-mono text-sm">{{ trim($record->doklad?->KlicDokla ?? '') ?: '—' }}</td>
                                <td>{{ trim($record->operation?->Nazev1 ?? $record->operation_id ?? '') }}</td>
                                <td>{{ $record->processed_quantity ?? 0 }} ks</td>
                                <td class="whitespace-nowrap tabular-nums">
                                    @if($info['workedH'] !== null)
                                        {{ $info['workedH'] }}:{{ str_pad($info['workedM'], 2, '0', STR_PAD_LEFT) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="max-w-48 truncate">{{ $record->notes ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 text-sm text-gray-500">
                @php
                    $totalMin = $this->records->sum(function ($r) {
                        if ($r->started_at && $r->ended_at) {
                            return max(0, intval($r->started_at->diffInMinutes($r->ended_at)) - ($r->total_paused_min ?? 0));
                        }
                        return 0;
                    });
                @endphp
                Celkem {{ $this->records->count() }} záznamů | Celkem: {{ intdiv($totalMin, 60) }}h {{ $totalMin % 60 }}min
            </div>
        @endif
    </x-mary-card>
</div>
