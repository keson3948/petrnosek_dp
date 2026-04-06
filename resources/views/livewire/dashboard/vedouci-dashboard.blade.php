<div>
    <x-mary-header title="Dashboard" separator />

    {{-- ===== NA ČEM SE PRACUJE ===== --}}
    <x-mary-card class="mb-8">
        <x-slot:title>
            <div class="flex items-center gap-2">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-success"></span>
                </span>
                Na čem se pracuje
            </div>
        </x-slot:title>

        {{-- Tabs: Vše + haly --}}
        @if($hallTabs->count() > 1)
            <div class="tabs tabs-bordered mb-4">
                <button wire:click="setTab('all')" class="tab {{ $activeTab === 'all' ? 'tab-active' : '' }}">
                    Vše
                </button>
                @foreach($hallTabs as $hall)
                    <button wire:click="setTab('{{ $hall }}')" class="tab {{ $activeTab === $hall ? 'tab-active' : '' }}">
                        {{ $hall }}
                    </button>
                @endforeach
            </div>
        @endif

        @if($activeRows->isEmpty())
            <div class="text-center py-8 text-gray-500">
                <x-mary-icon name="o-pause-circle" class="w-10 h-10 mx-auto text-gray-300 mb-2" />
                @if($activeTab !== 'all')
                    V hale {{ $activeTab }} nikdo právě nepracuje.
                @else
                    Nikdo právě nepracuje.
                @endif
            </div>
        @else
            <x-mary-table :headers="$activeHeaders" :rows="$activeRows" striped>
                @scope('cell_mistr', $record)
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-white font-bold text-xs shrink-0"
                         style="background-color: {{ $record->_mistr_color }}">
                        {{ $record->_mistr_cislo }}
                    </div>
                @endscope

                @scope('cell_vp', $record)
                    @if($record->_vp_sys_klic)
                        <a href="{{ route('vp.show', $record->_vp_sys_klic) }}" class="font-mono text-sm font-semibold hover:underline hover:text-primary">{{ $record->_vp_label }}</a>
                    @else
                        <span class="font-mono text-sm">—</span>
                    @endif
                @endscope

                @scope('cell_symbol', $record)
                    <span class="font-mono text-sm">{{ $record->_spec_symbol }}</span>
                @endscope

                @scope('cell_termin', $record)
                    <span class="whitespace-nowrap tabular-nums text-sm">
                        {{ $record->_termin ? \Carbon\Carbon::parse($record->_termin)->format('d.m.Y') : '—' }}
                    </span>
                @endscope

                @scope('cell_operator', $record)
                    @if($record->_user_klic)
                        <a href="{{ route('vedouci.show', $record->_user_klic) }}" class="font-semibold hover:underline hover:text-primary">{{ $record->_operator_name }}</a>
                    @else
                        <span class="font-semibold">{{ $record->_operator_name }}</span>
                    @endif
                @endscope

                @scope('cell_machine', $record)
                    @if($record->_machine_exists)
                        <a href="{{ route('vedouci.machine', $record->_machine_key) }}" class="hover:underline hover:text-primary">{{ $record->_machine_name }}</a>
                    @else
                        {{ $record->_machine_name }}
                    @endif
                @endscope

                @scope('cell_operation', $record)
                    {{ $record->_operation_name }}
                @endscope

                @scope('cell_time', $record)
                    @if($record->started_at)
                        @php
                            $now = now();
                            $elapsed = (int) $record->started_at->diffInMinutes($now);
                            $paused = (int) ($record->total_paused_min ?? 0);
                            if ($record->status === 1 && $record->last_paused_at) {
                                $paused += (int) \Carbon\Carbon::parse($record->last_paused_at)->diffInMinutes($now);
                            }
                            $worked = max(0, $elapsed - $paused);
                        @endphp
                        <span class="font-mono font-bold tabular-nums">{{ intdiv($worked, 60) }}:{{ str_pad($worked % 60, 2, '0', STR_PAD_LEFT) }}</span>
                    @else
                        <span class="text-gray-400">—</span>
                    @endif
                @endscope
            </x-mary-table>
        @endif
    </x-mary-card>

    {{-- ===== ROZPRACOVANÉ VP ===== --}}
    <x-mary-card>
        <x-slot:title>Rozpracované výrobní příkazy</x-slot:title>

        <div class="flex flex-wrap items-end gap-4 mb-4">
            <x-mary-input icon="o-magnifying-glass" wire:model.live.debounce.300ms="search" placeholder="Hledat VP..." clearable class="w-64" />
            <x-mary-select
                wire:model.live="filterMistr"
                :options="collect($mistrOptions)"
                option-value="id"
                option-label="name"
                placeholder="Všichni mistři"
                class="w-48"
            />
        </div>

        <x-mary-table :headers="$vpHeaders" :rows="$vpRows" with-pagination striped>
            @scope('cell_mistr_avatar', $row)
                <div class="w-9 h-9 rounded-full flex items-center justify-center text-white font-bold text-xs shrink-0"
                     style="background-color: {{ $row->mistr_color ?? '#6b7280' }}">
                    {{ $row->mistr_cislo ?? '' }}
                </div>
            @endscope

            @scope('cell_vp_name', $row)
                <a href="{{ route('vp.show', $row->doklad_id) }}" class="font-mono font-semibold hover:underline hover:text-primary">
                    {{ $row->mps_projekt }} {{ $row->klic_dokla }}
                </a>
            @endscope

            @scope('cell_specificky_symbol', $row)
                <span class="font-mono text-sm">{{ $row->specificky_symbol }}</span>
            @endscope

            @scope('cell_termin', $row)
                <span class="whitespace-nowrap tabular-nums">
                    {{ $row->termin_datum && $row->termin_datum !== '-' ? \Carbon\Carbon::parse($row->termin_datum)->format('d.m.Y') : '—' }}
                </span>
            @endscope
        </x-mary-table>
    </x-mary-card>
</div>
