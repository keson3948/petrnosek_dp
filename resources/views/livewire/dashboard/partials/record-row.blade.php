@php
    /** @var \App\Models\ProductionRecord $record */
    /** @var bool $isHistory */
    $isLunch = (int) ($record->TypZaznamu ?? 0) === 1;
@endphp

@if($isLunch)
    <div class="mb-2 flex items-center gap-3 px-3 py-2 rounded-lg border border-warning/30 bg-warning/5">
        <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center shrink-0 bg-warning/20 text-warning">
            <x-mary-icon name="o-cake" class="w-5 h-5" />
        </div>
        <div class="flex-1 min-w-0">
            <div class="font-semibold text-sm sm:text-base text-warning">Oběd</div>
            <div class="text-xs text-base-content/60">
                {{ \Carbon\Carbon::parse($record->started_at)->format('d.m. H:i') }}
                @if($record->ended_at)
                    – {{ \Carbon\Carbon::parse($record->ended_at)->format('H:i') }}
                @endif
            </div>
        </div>
        <div class="text-sm font-mono font-semibold text-warning shrink-0">30 min</div>
    </div>
@else

@php
    $info = $this->getRecordInfo($record);
    $workedH = $info['workedH'];
    $workedM = $info['workedM'];
    $mistrColor = $info['mistrColor'];
    $mistrCislo = $info['mistrCislo'];

    $emptyLabel = $isHistory ? '—' : 'Nezadáno';
@endphp

<x-mary-collapse class="mb-2 border {{ $isHistory ? 'bg-base-200 opacity-80 border-base-300' : 'border-base-200 bg-white' }}">
    <x-slot:heading>
        <div class="flex items-center gap-3 sm:gap-4 w-full {{ $isHistory ? 'grayscale-50' : '' }}">
            {{-- Avatar mistra --}}
            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center shrink-0 text-white font-bold text-xs sm:text-sm"
                 style="background-color: {{ $mistrColor }}">
                {{ $mistrCislo }}
            </div>

            {{-- VP + výkres --}}
            <div class="flex-1 sm:w-1/5 sm:flex-none min-w-0">
                <div class="font-bold truncate text-sm sm:text-base">
                    @if($isHistory && $record->ZakVP_SysPrimKlic)
                        <a href="{{ route('vp.show', trim($record->ZakVP_SysPrimKlic)) }}" class="z-50 relative hover:underline hover:text-primary">{{ trim($record->doklad?->MPSProjekt ?? '') ?: '—' }} {{ trim($record->doklad?->KlicDokla ?? '') ?: '—' }}</a>
                    @else
                        {{ trim($record->doklad?->MPSProjekt ?? '') ?: '' }} {{ trim($record->doklad?->KlicDokla ?? '') ?: '—' }}
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

            {{-- Operace + stroj --}}
            <div class="hidden md:block flex-1 min-w-0">
                <div class="text-sm truncate">{{ trim($record->operation?->Nazev1 ?? $record->operation_id) }}</div>
                @if($record->machine_id)
                    <div class="text-xs text-gray-500 truncate">{{ trim($record->machine?->NazevUplny ?? $record->machine_id) }}</div>
                @endif
            </div>

            {{-- Operátor + čas/datum --}}
            <div class="w-28 min-w-0 hidden lg:block">
                <div class="text-sm truncate">{{ auth()->user()->name }}</div>
                <div class="text-xs text-gray-500">{{ $record->started_at?->format($isHistory ? 'd.m.' : 'H:i') }}</div>
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
                    <x-mary-icon name="o-truck" class="w-5 h-5 {{ $isHistory ? 'text-neutral opacity-50' : 'text-success' }}" title="Služební cesta" />
                @else
                    <x-mary-icon name="o-check-circle" class="w-6 h-6 sm:w-7 sm:h-7 {{ $isHistory ? 'text-neutral opacity-50' : 'text-success' }}" />
                @endif
            </div>
        </div>
    </x-slot:heading>
    <x-slot:content>
        <div class="p-4 {{ $isHistory ? 'border-t border-base-300' : 'bg-base-50' }}">
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
                        <div class="font-semibold">{{ trim($record->machine?->NazevUplny ?? $record->machine_id) ?: $emptyLabel }} <span class="text-gray-400 mx-1">/</span> {{ trim($record->operation?->Nazev1 ?? $record->operation_id) }}</div>
                    </div>
                    <button wire:click="openEditMachineOp({{ $record->ID }})" class="btn btn-ghost btn-sm btn-square" title="Upravit stroj a operaci">
                        <x-mary-icon name="o-pencil" class="w-5 h-5 text-primary" />
                    </button>
                </div>

                <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-base-200">
                    <div>
                        <div class="text-xs text-gray-400 uppercase tracking-wide">Řádek / Podsestava</div>
                        <div class="font-semibold">{{ $record->ZakVP_pozice_radku ? 'Poz. ' . $record->ZakVP_pozice_radku : $emptyLabel }} <span class="text-gray-400 mx-1">/</span> {{ $record->podsestav?->OznaceniPodsestavy ?: '—' }}</div>
                    </div>
                    <button wire:click="openEditRadekPodsestava({{ $record->ID }})" class="btn btn-ghost btn-sm btn-square" title="Upravit řádek a podsestavu">
                        <x-mary-icon name="o-pencil" class="w-5 h-5 text-primary" />
                    </button>
                </div>

                <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-base-200">
                    <div>
                        <div class="text-xs text-gray-400 uppercase tracking-wide">Výkres</div>
                        <div class="font-semibold">{{ $record->drawing_number ?: $emptyLabel }}</div>
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
                        <div class="font-semibold {{ $isHistory ? '' : 'text-lg' }}">
                            @if($workedH !== null)
                                {{ $workedH }}h {{ $workedM }}min
                            @else
                                {{ $record->started_at?->format('H:i') }} - {{ $record->ended_at?->format('H:i') ?? '?' }}
                            @endif
                        </div>
                        <div class="text-xs text-gray-400">
                            {{ $record->started_at?->format('d.m.Y H:i') }} - {{ $record->ended_at?->format('H:i') ?? '?' }}
                            @if(!$isHistory && $record->total_paused_seconds > 0)
                                (Pauza: {{ gmdate("H:i:s", $record->total_paused_seconds) }})
                            @endif
                        </div>
                    </div>
                    @if(auth()->user()->can('edit production record time') || !empty($record->SluzebniCesta))
                        <button wire:click="openEditTime({{ $record->ID }})" class="btn btn-ghost btn-sm btn-square" title="Upravit čas">
                            <x-mary-icon name="o-pencil" class="w-5 h-5 text-primary" />
                        </button>
                    @endif
                </div>

                <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-base-200 md:col-span-2">
                    <div class="min-w-0 flex-1">
                        <div class="text-xs text-gray-400 uppercase tracking-wide">Poznámka</div>
                        <div class="font-semibold truncate">{{ $record->notes ?: $emptyLabel }}</div>
                    </div>
                    <button wire:click="openEditNotes({{ $record->ID }})" class="btn btn-ghost btn-sm btn-square shrink-0" title="Upravit poznámku">
                        <x-mary-icon name="o-pencil" class="w-5 h-5 text-primary" />
                    </button>
                </div>
            </div>
        </div>
    </x-slot:content>
</x-mary-collapse>
@endif
