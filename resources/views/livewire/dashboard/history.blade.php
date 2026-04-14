<div>
    @if($activeTrips->isNotEmpty())
        <div class="mt-8">
            <x-mary-card title="Naplánované služební cesty" class="bg-base-200 border-0 shadow-none p-0!">
                @foreach($activeTrips as $trip)
                    <div
                        wire:click="startTrip('{{ trim($trip->KlicSluzebniCesty) }}')"
                        class="mb-2 p-4 border border-base-300 bg-white rounded-box cursor-pointer hover:bg-info/5 transition flex items-center gap-4"
                    >
                        <x-mary-icon name="o-truck" class="text-info w-8 h-8 shrink-0" />
                        <div class="flex-1 min-w-0">
                            <div class="font-bold text-info">
                                {{ trim($trip->Nazev ?? '') ?: 'Služební cesta' }}
                            </div>
                            <div class="text-sm text-gray-500 flex flex-wrap gap-x-3">
                                <span>{{ $trip->DatumACasOd?->format('d.m.') ?? '' }} – {{ $trip->DatumACasDo?->format('d.m.') ?? '' }}</span>
                                @if(trim($trip->zakaznikSubjekt->Nazev1 ?? ''))
                                    <span>{{ trim($trip->zakaznikSubjekt->Nazev1) }}</span>
                                @endif
                                @if($trip->doklad)
                                    <span class="font-mono">{{ trim($trip->doklad->MPSProjekt ?? '') }} {{ trim($trip->doklad->KlicDokla ?? '') }}</span>
                                @endif
                            </div>
                        </div>
                        <x-mary-icon name="o-play" class="text-info w-6 h-6 shrink-0" />
                    </div>
                @endforeach
            </x-mary-card>
        </div>
    @endif

    <div class="mt-8">
        <x-mary-card title="Dnešní směna" class="bg-transparent border-0 shadow-none p-0!">
            @forelse($today as $record)
                @php
                    $info = $this->getRecordInfo($record);
                    $workedH = $info['workedH'];
                    $workedM = $info['workedM'];
                    $mistrColor = $info['mistrColor'];
                    $mistrCislo = $info['mistrCislo'];
                @endphp
                <x-mary-collapse class="mb-2 border border-base-200 bg-white">
                    <x-slot:heading>
                        <div class="flex items-center gap-3 sm:gap-4 w-full">
                            {{-- Avatar mistra --}}
                            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center shrink-0 text-white font-bold text-xs sm:text-sm"
                                 style="background-color: {{ $mistrColor }}">
                                {{ $mistrCislo }}
                            </div>
                            {{-- VP + výkres --}}
                            <div class="flex-1 sm:w-1/5 sm:flex-none min-w-0">
                                <div class="font-bold truncate text-sm sm:text-base">{{ trim($record->doklad?->MPSProjekt ?? '') ?: '' }} {{ trim($record->doklad?->KlicDokla ?? '') ?: '—' }}</div>
                                @if($record->drawing_number)
                                    <div class="text-xs text-gray-500 truncate">{{ $record->drawing_number }}</div>
                                @endif
                            </div>

                            {{-- Množství --}}
                            <div class="hidden sm:block w-1/6 min-w-0">
                                <div class="text-sm font-semibold">{{ trim($record->doklad?->SpecifiSy ?? '') ?: '—' }}</div>
                                <div class="text-sm font-semibold">{{ $record->processed_quantity }} ks</div>
                            </div>

                            {{-- Operace + stroj --}}
                            <div class="hidden md:block flex-1 min-w-0">
                                <div class="text-sm truncate">{{ trim($record->operation?->Nazev1 ?? $record->operation_id) }}</div>
                                @if($record->machine_id)
                                    <div class="text-xs text-gray-500 truncate">{{ trim($record->machine?->NazevUplny ?? $record->machine_id) }}</div>
                                @endif
                            </div>

                            {{-- Operátor + čas zahájení --}}
                            <div class="w-28 min-w-0 hidden lg:block">
                                <div class="text-sm truncate">{{ auth()->user()->name }}</div>
                                <div class="text-xs text-gray-500">{{ $record->started_at?->format('H:i') }}</div>
                            </div>

                            {{-- Odpracovaný čas --}}
                            <div class="w-14 sm:w-16 text-right shrink-0">
                                @if($workedH !== null)
                                    <span class="text-xl sm:text-2xl font-bold tabular-nums">{{ $workedH }}:{{ str_pad($workedM, 2, '0', STR_PAD_LEFT) }}</span>
                                @else
                                    <span class="text-sm text-gray-400">—</span>
                                @endif
                            </div>

                            {{-- Stav --}}
                            <div class="shrink-0 flex items-center gap-1.5 justify-end">
                                @if($record->SluzebniCesta)
                                    <x-mary-icon name="o-truck" class="text-info w-5 h-5" title="Služební cesta" />
                                @endif
                                <x-mary-icon name="o-check-circle" class="text-success w-6 h-6 sm:w-7 sm:h-7" />
                            </div>
                        </div>
                    </x-slot:heading>
                    <x-slot:content>
                        <div class="p-4 bg-base-50">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-base-200">
                                    <div>
                                        <div class="text-xs text-gray-400 uppercase tracking-wide">Výrobní příkaz (VP)</div>
                                        <div class="font-semibold text-lg">
                                            @if($record->ZakVP_SysPrimKlic)
                                                <a href="{{ route('vp.show', trim($record->ZakVP_SysPrimKlic)) }}" @click.stop class="z-50 relative hover:underline hover:text-primary">{{ trim($record->doklad?->MPSProjekt ?? '') ?: '' }} {{ trim($record->doklad?->KlicDokla ?? '') ?: '—' }}</a>
                                            @else
                                                {{ trim($record->doklad?->MPSProjekt ?? '') ?: '' }} {{ trim($record->doklad?->KlicDokla ?? '') ?: '—' }}
                                            @endif
                                            @if(trim($record->doklad?->SpecifiSy ?? ''))
                                                <span class="text-gray-400 mx-1">|</span> {{ trim($record->doklad->SpecifiSy) }}
                                            @endif
                                        </div>
                                    </div>
                                    <button wire:click="openEditVp({{ $record->ID }})" class="btn btn-ghost btn-sm btn-square" title="Upravit VP">
                                        <x-mary-icon name="o-pencil" class="w-5 h-5 text-primary" />
                                    </button>
                                </div>

                                <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-base-200">
                                    <div>
                                        <div class="text-xs text-gray-400 uppercase tracking-wide">Stroj / Operace</div>
                                        <div class="font-semibold">{{ trim($record->machine?->NazevUplny ?? $record->machine_id) ?: 'Nezadáno' }} <span class="text-gray-400 mx-1">/</span> {{ trim($record->operation?->Nazev1 ?? $record->operation_id) }}</div>
                                    </div>
                                    <button wire:click="openEditMachineOp({{ $record->ID }})" class="btn btn-ghost btn-sm btn-square" title="Upravit stroj a operaci">
                                        <x-mary-icon name="o-pencil" class="w-5 h-5 text-primary" />
                                    </button>
                                </div>

                                <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-base-200">
                                    <div>
                                        <div class="text-xs text-gray-400 uppercase tracking-wide">Řádek / Podsestava</div>
                                        <div class="font-semibold">{{ $record->ZakVP_pozice_radku ? 'Poz. ' . $record->ZakVP_pozice_radku : 'Nezadáno' }} <span class="text-gray-400 mx-1">/</span> {{ $record->podsestav?->OznaceniPodsestavy ?: '—' }} </div>
                                    </div>
                                    <button wire:click="openEditRadekPodsestava({{ $record->ID }})" class="btn btn-ghost btn-sm btn-square" title="Upravit řádek a podsestavu">
                                        <x-mary-icon name="o-pencil" class="w-5 h-5 text-primary" />
                                    </button>
                                </div>

                                <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-base-200">
                                    <div>
                                        <div class="text-xs text-gray-400 uppercase tracking-wide">Výkres</div>
                                        <div class="font-semibold">{{ $record->drawing_number ?: 'Nezadáno' }}</div>
                                    </div>
                                    <button wire:click="openEditDrawing({{ $record->ID }})" class="btn btn-ghost btn-sm btn-square" title="Upravit výkres">
                                        <x-mary-icon name="o-pencil" class="w-5 h-5 text-primary" />
                                    </button>
                                </div>

                                <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-base-200">
                                    <div>
                                        <div class="text-xs text-gray-400 uppercase tracking-wide">Množství</div>
                                        <div class="font-semibold text-lg">{{ $record->processed_quantity }} ks</div>
                                    </div>
                                    <button wire:click="openEditQuantity({{ $record->ID }})" class="btn btn-ghost btn-sm btn-square" title="Upravit množství">
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
                                                {{ $record->started_at?->format('H:i') }} - {{ $record->ended_at?->format('H:i') ?? '?' }}
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-400">
                                            {{ $record->started_at?->format('d.m.Y H:i') }} - {{ $record->ended_at?->format('H:i') ?? '?' }}
                                            @if($record->total_paused_seconds > 0)
                                                (Pauza: {{ gmdate("H:i:s", $record->total_paused_seconds) }})
                                            @endif
                                        </div>
                                    </div>
                                    <button wire:click="openEditTime({{ $record->ID }})" class="btn btn-ghost btn-sm btn-square" title="Upravit čas">
                                        <x-mary-icon name="o-pencil" class="w-5 h-5 text-primary" />
                                    </button>
                                </div>

                                <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-base-200 md:col-span-2">
                                    <div class="min-w-0 flex-1">
                                        <div class="text-xs text-gray-400 uppercase tracking-wide">Poznámka</div>
                                        <div class="font-semibold truncate">{{ $record->notes ?: 'Nezadáno' }}</div>
                                    </div>
                                    <button wire:click="openEditNotes({{ $record->ID }})" class="btn btn-ghost btn-sm btn-square shrink-0" title="Upravit poznámku">
                                        <x-mary-icon name="o-pencil" class="w-5 h-5 text-primary" />
                                    </button>
                                </div>
                            </div>
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
            <x-mary-card title="Historie (Posledních 5 dní)" class="bg-transparent border-0 shadow-none p-0! mt-6">
                @foreach($historical as $record)
                    @php
                        $info = $this->getRecordInfo($record);
                        $workedH = $info['workedH'];
                        $workedM = $info['workedM'];
                        $mistrColor = $info['mistrColor'];
                        $mistrCislo = $info['mistrCislo'];
                    @endphp
                    <x-mary-collapse class="bg-base-200 opacity-80 mb-2 border border-base-300">
                        <x-slot:heading>
                            <div class="flex items-center gap-3 sm:gap-4 w-full grayscale-50">
                                {{-- Avatar mistra --}}
                                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center shrink-0 text-white font-bold text-xs sm:text-sm"
                                     style="background-color: {{ $mistrColor }}">
                                    {{ $mistrCislo }}
                                </div>

                                {{-- VP + výkres --}}
                                <div class="flex-1 sm:w-1/5 sm:flex-none min-w-0">
                                    <div class="font-bold truncate text-sm sm:text-base">
                                        @if($record->ZakVP_SysPrimKlic)
                                            <a href="{{ route('vp.show', trim($record->ZakVP_SysPrimKlic)) }}" class="z-50 relative hover:underline hover:text-primary">{{ trim($record->doklad?->MPSProjekt ?? '') ?: '—' }} {{ trim($record->doklad?->KlicDokla ?? '') ?: '—' }}</a>
                                        @else
                                            {{ trim($record->doklad?->MPSProjekt ?? '') ?: '—' }} {{ trim($record->doklad?->KlicDokla ?? '') ?: '—' }}
                                        @endif
                                    </div>
                                    @if($record->drawing_number)
                                        <div class="text-xs text-gray-500 truncate">{{ $record->drawing_number }}</div>
                                    @endif
                                </div>

                                {{-- Množství --}}
                                <div class="hidden sm:block w-1/6 min-w-0">
                                    <div class="text-sm font-semibold">{{ trim($record->doklad?->SpecifiSy ?? '') ?: '—' }}</div>
                                    <div class="text-sm font-semibold">{{ $record->processed_quantity }} ks</div>
                                </div>

                                <div class="hidden md:block flex-1 min-w-0">
                                    <div class="text-sm truncate">{{ trim($record->operation?->Nazev1 ?? $record->operation_id) }}</div>
                                    @if($record->machine_id)
                                        <div class="text-xs text-gray-500 truncate">{{ trim($record->machine?->NazevUplny ?? $record->machine_id) }}</div>
                                    @endif
                                </div>

                                {{-- Operátor + datum --}}
                                <div class="w-28 min-w-0 hidden lg:block">
                                    <div class="text-sm truncate">{{ auth()->user()->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $record->started_at?->format('d.m.') }}</div>
                                </div>

                                {{-- Odpracovaný čas --}}
                                <div class="w-14 sm:w-16 text-right shrink-0">
                                    @if($workedH !== null)
                                        <span class="text-xl sm:text-2xl font-bold tabular-nums">{{ $workedH }}:{{ str_pad($workedM, 2, '0', STR_PAD_LEFT) }}</span>
                                    @else
                                        <span class="text-sm text-gray-400">—</span>
                                    @endif
                                </div>

                                {{-- Badge --}}
                                <div class="shrink-0 flex items-center gap-1.5 justify-end">
                                    @if($record->SluzebniCesta)
                                        <x-mary-icon name="o-truck" class="text-info w-5 h-5 opacity-50" title="Služební cesta" />
                                    @endif
                                    <x-mary-icon name="o-check-circle" class="text-neutral w-6 h-6 sm:w-7 sm:h-7 opacity-50" />
                                </div>
                            </div>
                        </x-slot:heading>
                        <x-slot:content>
                            <div class="p-4 border-t border-base-300">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-base-200">
                                        <div>
                                            <div class="text-xs text-gray-400 uppercase tracking-wide">Výrobní příkaz (VP)</div>
                                            <div class="font-semibold text-lg">
                                                @if($record->ZakVP_SysPrimKlic)
                                                    <a href="{{ route('vp.show', trim($record->ZakVP_SysPrimKlic)) }}" @click.stop class="z-50 relative hover:underline hover:text-primary">{{ trim($record->doklad?->MPSProjekt ?? '') ?: '' }} {{ trim($record->doklad?->KlicDokla ?? '') ?: '—' }}</a>
                                                @else
                                                    {{ trim($record->doklad?->MPSProjekt ?? '') ?: '' }} {{ trim($record->doklad?->KlicDokla ?? '') ?: '—' }}
                                                @endif
                                                @if(trim($record->doklad?->SpecifiSy ?? ''))
                                                    <span class="text-gray-400 mx-1">|</span> {{ trim($record->doklad->SpecifiSy) }}
                                                @endif
                                            </div>
                                        </div>
                                        <button wire:click="openEditVp({{ $record->ID }})" class="btn btn-ghost btn-sm btn-square">
                                            <x-mary-icon name="o-pencil" class="w-5 h-5 text-primary" />
                                        </button>
                                    </div>

                                    <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-base-200">
                                        <div>
                                            <div class="text-xs text-gray-400 uppercase tracking-wide">Stroj / Operace</div>
                                            <div class="font-semibold">{{ trim($record->machine?->NazevUplny ?? $record->machine_id) ?: '—' }} / {{ trim($record->operation?->Nazev1 ?? $record->operation_id) }}</div>
                                        </div>
                                        <button wire:click="openEditMachineOp({{ $record->ID }})" class="btn btn-ghost btn-sm btn-square">
                                            <x-mary-icon name="o-pencil" class="w-5 h-5 text-primary" />
                                        </button>
                                    </div>

                                    <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-base-200">
                                        <div>
                                            <div class="text-xs text-gray-400 uppercase tracking-wide">Řádek / Podsestava</div>
                                            <div class="font-semibold">{{ $record->ZakVP_pozice_radku ? 'Poz. ' . $record->ZakVP_pozice_radku : '—' }} <span class="text-gray-400 mx-1">/</span> {{ $record->podsestav?->OznaceniPodsestavy ?: '—' }}</div>
                                        </div>
                                        <button wire:click="openEditRadekPodsestava({{ $record->ID }})" class="btn btn-ghost btn-sm btn-square">
                                            <x-mary-icon name="o-pencil" class="w-5 h-5 text-primary" />
                                        </button>
                                    </div>

                                    <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-base-200">
                                        <div>
                                            <div class="text-xs text-gray-400 uppercase tracking-wide">Výkres</div>
                                            <div class="font-semibold">{{ $record->drawing_number ?: '—' }}</div>
                                        </div>
                                        <button wire:click="openEditDrawing({{ $record->ID }})" class="btn btn-ghost btn-sm btn-square">
                                            <x-mary-icon name="o-pencil" class="w-5 h-5 text-primary" />
                                        </button>
                                    </div>

                                    <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-base-200">
                                        <div>
                                            <div class="text-xs text-gray-400 uppercase tracking-wide">Množství</div>
                                            <div class="font-semibold text-lg">{{ $record->processed_quantity }} ks</div>
                                        </div>
                                        <button wire:click="openEditQuantity({{ $record->ID }})" class="btn btn-ghost btn-sm btn-square">
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
                                                    {{ $record->started_at?->format('H:i') }} - {{ $record->ended_at?->format('H:i') ?? '?' }}
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-400">
                                                {{ $record->started_at?->format('d.m.Y H:i') }} - {{ $record->ended_at?->format('H:i') ?? '?' }}
                                            </div>
                                        </div>
                                        <button wire:click="openEditTime({{ $record->ID }})" class="btn btn-ghost btn-sm btn-square">
                                            <x-mary-icon name="o-pencil" class="w-5 h-5 text-primary" />
                                        </button>
                                    </div>

                                    <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-base-200 md:col-span-2">
                                        <div class="min-w-0 flex-1">
                                            <div class="text-xs text-gray-400 uppercase tracking-wide">Poznámka</div>
                                            <div class="font-semibold truncate">{{ $record->notes ?: '—' }}</div>
                                        </div>
                                        <button wire:click="openEditNotes({{ $record->ID }})" class="btn btn-ghost btn-sm btn-square shrink-0">
                                            <x-mary-icon name="o-pencil" class="w-5 h-5 text-primary" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </x-slot:content>
                    </x-mary-collapse>
                @endforeach
            </x-mary-card>
        @endif
    </div>

    @include('livewire.dashboard.partials.edit-modals')

</div>
