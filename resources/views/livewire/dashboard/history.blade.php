<div>
    <div class="mt-8">
        <x-mary-card title="Dnešní směna" class="bg-transparent border-0 shadow-none !p-0">
            @forelse($today as $record)
                @php
                    $info = $this->getRecordInfo($record, $mistrUsers);
                    $workedH = $info['workedH'];
                    $workedM = $info['workedM'];
                    $mistrColor = $info['mistrColor'];
                    $mistrCislo = $info['mistrCislo'];
                @endphp
                <x-mary-collapse class="mb-2 border border-base-200 bg-white">
                    <x-slot:heading>
                        <div class="flex items-center gap-4 w-full">
                            {{-- Avatar mistra --}}
                            <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 text-white font-bold text-sm"
                                 style="background-color: {{ $mistrColor }}">
                                {{ $mistrCislo }}
                            </div>

                            {{-- VP + výkres --}}
                            <div class="w-1/5 min-w-0">
                                <div class="font-bold truncate">{{ trim($record->doklad?->MPSProjekt ?? '') ?: '—' }} {{ trim($record->doklad?->KlicDokla ?? '') ?: '—' }}</div>
                                @if($record->drawing_number)
                                    <div class="text-xs text-gray-500 truncate">{{ $record->drawing_number }}</div>
                                @endif
                            </div>

                            {{-- Množství --}}
                            <div class="w-1/6 min-w-0">
                                <div class="text-sm font-semibold">{{ $record->processed_quantity }} ks</div>
                            </div>

                            {{-- Operace + stroj --}}
                            <div class="flex-1 min-w-0">
                                <div class="text-sm truncate">{{ trim($record->operation?->Nazev1 ?? $record->operation_id) }}</div>
                                @if($record->machine_id)
                                    <div class="text-xs text-gray-500 truncate">{{ trim($record->machine?->NazevUplny ?? $record->machine_id) }}</div>
                                @endif
                            </div>

                            {{-- Operátor + čas zahájení --}}
                            <div class="w-28 min-w-0 hidden md:block">
                                <div class="text-sm truncate">{{ auth()->user()->name }}</div>
                                <div class="text-xs text-gray-500">{{ $record->started_at->format('H:i') }}</div>
                            </div>

                            {{-- Odpracovaný čas --}}
                            <div class="w-16 text-right shrink-0">
                                @if($workedH !== null)
                                    <span class="text-2xl font-bold tabular-nums">{{ $workedH }}:{{ str_pad($workedM, 2, '0', STR_PAD_LEFT) }}</span>
                                @else
                                    <span class="text-sm text-gray-400">—</span>
                                @endif
                            </div>

                            {{-- Stav --}}
                            <div class="w-8 shrink-0 flex justify-end">
                                <x-mary-icon name="o-check-circle" class="text-success w-7 h-7" />
                            </div>
                        </div>
                    </x-slot:heading>
                    <x-slot:content>
                        <div class="p-4 border-t bg-base-50">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-base-200">
                                    <div>
                                        <div class="text-xs text-gray-400 uppercase tracking-wide">Výrobní příkaz (VP)</div>
                                        <div class="font-semibold text-lg">{{ trim($record->doklad?->KlicDokla ?? '') ?: '—' }}</div>
                                    </div>
                                    <button wire:click="openEditVp({{ $record->id }})" class="btn btn-ghost btn-sm btn-square" title="Upravit VP">
                                        <x-mary-icon name="o-pencil" class="w-5 h-5 text-primary" />
                                    </button>
                                </div>

                                <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-base-200">
                                    <div>
                                        <div class="text-xs text-gray-400 uppercase tracking-wide">Stroj / Operace</div>
                                        <div class="font-semibold">{{ trim($record->machine?->NazevUplny ?? $record->machine_id) ?: 'Nezadáno' }} <span class="text-gray-400 mx-1">/</span> {{ trim($record->operation?->Nazev1 ?? $record->operation_id) }}</div>
                                    </div>
                                    <button wire:click="openEditMachineOp({{ $record->id }})" class="btn btn-ghost btn-sm btn-square" title="Upravit stroj a operaci">
                                        <x-mary-icon name="o-pencil" class="w-5 h-5 text-primary" />
                                    </button>
                                </div>

                                <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-base-200">
                                    <div>
                                        <div class="text-xs text-gray-400 uppercase tracking-wide">Odpracovaný čas</div>
                                        <div class="font-semibold text-lg">
                                            @if($workedH !== null)
                                                {{ $workedH }}h {{ $workedM }}min
                                            @else
                                                {{ $record->started_at->format('H:i') }} - {{ $record->ended_at?->format('H:i') ?? '?' }}
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-400">
                                            {{ $record->started_at->format('d.m.Y H:i') }} - {{ $record->ended_at?->format('H:i') ?? '?' }}
                                            @if($record->total_paused_seconds > 0)
                                                (Pauza: {{ gmdate("H:i:s", $record->total_paused_seconds) }})
                                            @endif
                                        </div>
                                    </div>
                                    <button wire:click="openEditTime({{ $record->id }})" class="btn btn-ghost btn-sm btn-square" title="Upravit čas">
                                        <x-mary-icon name="o-pencil" class="w-5 h-5 text-primary" />
                                    </button>
                                </div>
                            </div>

                            <div class="mt-3 grid grid-cols-2 md:grid-cols-4 gap-3">
                                <div><strong>Operátor:</strong> {{ auth()->user()->name }}</div>
                                <div><strong>Množství:</strong> {{ $record->processed_quantity }} ks</div>
                            </div>

                            @if($record->notes)
                                <div class="mt-2">
                                    <strong>Poznámka:</strong> <span class="text-gray-600">{{ $record->notes }}</span>
                                </div>
                            @endif
                        </div>
                    </x-slot:content>
                </x-mary-collapse>
            @empty
                <div class="text-center py-6 text-gray-500 bg-white rounded-lg border border-dashed">
                    Dnes zatím nemáte žádné dokončené operace.
                </div>
            @endforelse
        </x-mary-card>

        @if($historical->count() > 0)
            <x-mary-card title="Historie (Posledních 5 dní)" class="bg-transparent border-0 shadow-none !p-0 mt-6">
                @foreach($historical as $record)
                    @php
                        $info = $this->getRecordInfo($record, $mistrUsers);
                        $workedH = $info['workedH'];
                        $workedM = $info['workedM'];
                        $mistrColor = $info['mistrColor'];
                        $mistrCislo = $info['mistrCislo'];
                    @endphp
                    <x-mary-collapse class="bg-base-200 opacity-80 mb-2 border border-base-300">
                        <x-slot:heading>
                            <div class="flex items-center gap-4 w-full grayscale-[50%]">
                                {{-- Avatar mistra --}}
                                <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 text-white font-bold text-sm"
                                     style="background-color: {{ $mistrColor }}">
                                    {{ $mistrCislo }}
                                </div>

                                {{-- VP + výkres --}}
                                <div class="w-1/5 min-w-0">
                                    <div class="font-bold truncate">{{ trim($record->doklad?->KlicDokla ?? '') ?: '—' }}</div>
                                    @if($record->drawing_number)
                                        <div class="text-xs text-gray-500 truncate">{{ $record->drawing_number }}</div>
                                    @endif
                                </div>

                                {{-- Množství --}}
                                <div class="w-1/6 min-w-0">
                                    <div class="text-sm font-semibold">{{ $record->processed_quantity }} ks</div>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="text-sm truncate">{{ trim($record->operation?->Nazev1 ?? $record->operation_id) }}</div>
                                    @if($record->machine_id)
                                        <div class="text-xs text-gray-500 truncate">{{ trim($record->machine?->NazevUplny ?? $record->machine_id) }}</div>
                                    @endif
                                </div>

                                {{-- Operátor + datum --}}
                                <div class="w-28 min-w-0 hidden md:block">
                                    <div class="text-sm truncate">{{ auth()->user()->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $record->started_at->format('d.m.') }}</div>
                                </div>

                                {{-- Odpracovaný čas --}}
                                <div class="w-16 text-right shrink-0">
                                    @if($workedH !== null)
                                        <span class="text-2xl font-bold tabular-nums">{{ $workedH }}:{{ str_pad($workedM, 2, '0', STR_PAD_LEFT) }}</span>
                                    @else
                                        <span class="text-sm text-gray-400">—</span>
                                    @endif
                                </div>

                                {{-- Badge --}}
                                <div class="w-8 shrink-0 flex justify-end">
                                    <x-mary-icon name="o-check-circle" class="text-neutral w-7 h-7 opacity-50" />
                                </div>
                            </div>
                        </x-slot:heading>
                        <x-slot:content>
                            <div class="p-4 border-t border-base-300">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-base-200">
                                        <div>
                                            <div class="text-xs text-gray-400 uppercase tracking-wide">Výrobní příkaz (VP)</div>
                                            <div class="font-semibold">{{ trim($record->doklad?->KlicDokla ?? '') ?: '—' }}</div>
                                        </div>
                                        <button wire:click="openEditVp({{ $record->id }})" class="btn btn-ghost btn-sm btn-square">
                                            <x-mary-icon name="o-pencil" class="w-5 h-5 text-primary" />
                                        </button>
                                    </div>

                                    <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-base-200">
                                        <div>
                                            <div class="text-xs text-gray-400 uppercase tracking-wide">Stroj / Operace</div>
                                            <div class="font-semibold">{{ trim($record->machine?->NazevUplny ?? $record->machine_id) ?: '—' }} / {{ trim($record->operation?->Nazev1 ?? $record->operation_id) }}</div>
                                        </div>
                                        <button wire:click="openEditMachineOp({{ $record->id }})" class="btn btn-ghost btn-sm btn-square">
                                            <x-mary-icon name="o-pencil" class="w-5 h-5 text-primary" />
                                        </button>
                                    </div>

                                    <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-base-200">
                                        <div>
                                            <div class="text-xs text-gray-400 uppercase tracking-wide">Odpracovaný čas</div>
                                            <div class="font-semibold">
                                                @if($workedH !== null)
                                                    {{ $workedH }}h {{ $workedM }}min
                                                @else
                                                    {{ $record->started_at->format('H:i') }} - {{ $record->ended_at?->format('H:i') ?? '?' }}
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-400">
                                                {{ $record->started_at->format('d.m.Y H:i') }} - {{ $record->ended_at?->format('H:i') ?? '?' }}
                                            </div>
                                        </div>
                                        <button wire:click="openEditTime({{ $record->id }})" class="btn btn-ghost btn-sm btn-square">
                                            <x-mary-icon name="o-pencil" class="w-5 h-5 text-primary" />
                                        </button>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <strong>Operátor:</strong> {{ auth()->user()->name }}
                                    <span class="mx-2">|</span>
                                    <strong>Množství:</strong> {{ $record->processed_quantity }} ks
                                </div>

                                @if($record->notes)
                                    <div class="mt-2">
                                        <strong>Poznámka:</strong> <span class="text-gray-600">{{ $record->notes }}</span>
                                    </div>
                                @endif
                            </div>
                        </x-slot:content>
                    </x-mary-collapse>
                @endforeach
            </x-mary-card>
        @endif
    </div>

    @include('livewire.dashboard.partials.edit-modals')

</div>
