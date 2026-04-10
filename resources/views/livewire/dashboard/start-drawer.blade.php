<div>
    <x-mary-drawer
        wire:model="showStartDrawer"
        right
        :title="$showTripConfirmation
            ? 'Služební cesta'
            : match($startStep) {
                1 => 'Vyberte výrobní příkaz',
                2 => 'Vyberte řádek VP',
                3 => 'Vyberte podsestavu',
                4 => 'Číslo výkresu',
                5 => 'Vyberte stroj',
                6 => 'Vyberte operaci',
                default => 'Nová operace',
            }"
        :subtitle="$showTripConfirmation ? '' : 'Krok ' . $startStep"
        separator
        with-close-button
        close-on-escape
        class="w-full lg:w-1/3"
    >
        <div class="flex flex-col h-[calc(100vh-10rem)]">

            @if($showTripConfirmation)
                @php $trip = $this->selectedTrip; @endphp
                @if($trip)
                    <div class="flex-1 overflow-y-auto min-h-0 space-y-4">
                        <div class="bg-info/10 border border-info/30 rounded-lg p-4">
                            <div class="flex items-center gap-2 mb-3">
                                <x-mary-icon name="o-truck" class="w-5 h-5 text-info" />
                                <span class="font-bold text-info">Služební cesta</span>
                            </div>

                            @if(trim($trip->Nazev ?? ''))
                                <div class="text-lg font-bold mb-2">{{ trim($trip->Nazev) }}</div>
                            @endif

                            <div class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-2 text-sm">
                                <span class="text-gray-400 font-bold uppercase text-xs">Datum</span>
                                <span class="font-semibold">
                                    {{ $trip->DatumACasOd?->format('d.m.Y') ?? '—' }}
                                    –
                                    {{ $trip->DatumACasDo?->format('d.m.Y') ?? '—' }}
                                </span>

                                @if($trip->doklad)
                                    <span class="text-gray-400 font-bold uppercase text-xs">VP</span>
                                    <span class="font-semibold">
                                        @if(trim($trip->doklad->MPSProjekt ?? ''))
                                            <span>{{ trim($trip->doklad->MPSProjekt) }}</span>
                                        @endif
                                        {{ trim($trip->doklad->KlicDokla ?? '') }}
                                    </span>
                                @endif

                                @if($trip->operace)
                                    <span class="text-gray-400 font-bold uppercase text-xs">Operace</span>
                                    <span class="font-semibold">{{ trim($trip->operace->Nazev1 ?? trim($trip->HlavniCinnost ?? '')) }}</span>
                                @endif

                                @if(trim($trip->zakaznikSubjekt->Nazev1 ?? '') || trim($trip->Zakaznik ?? ''))
                                    <span class="text-gray-400 font-bold uppercase text-xs">Zákazník</span>
                                    <span class="font-semibold">{{ trim($trip->zakaznikSubjekt->Nazev ?? '') ?: trim($trip->Zakaznik ?? '—') }}</span>
                                @endif

                                @if(trim($trip->Vozidlo ?? ''))
                                    <span class="text-gray-400 font-bold uppercase text-xs">Vozidlo</span>
                                    <span class="font-semibold">{{ trim($trip->Vozidlo) }}</span>
                                @endif

                                @if(trim($trip->pracovisteSubjekt->Nazev1 ?? '') || trim($trip->MistoRealizacePracoviste ?? ''))
                                    <span class="text-gray-400 font-bold uppercase text-xs">Pracoviště</span>
                                    <span class="font-semibold">{{ trim($trip->pracovisteSubjekt->Nazev ?? '') ?: trim($trip->MistoRealizacePracoviste ?? '—') }}</span>
                                @endif
                            </div>

                            @if(trim($trip->Poznamka ?? ''))
                                <div class="mt-3 pt-3 border-t border-info/20">
                                    <div class="text-xs text-gray-400 font-bold uppercase mb-1">Poznámka</div>
                                    <div class="text-sm whitespace-pre-wrap break-words">{{ trim($trip->Poznamka) }}</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Trip footer --}}
                    <div class="shrink-0 flex items-center justify-between gap-3 pt-4 border-t border-base-200 mt-4">
                        <x-mary-button label="Zpět" icon="o-arrow-left" wire:click="cancelTrip" class="btn-lg" />
                        <x-mary-button label="Zahájit služební cestu" icon="o-play" wire:click="startTripOperation" class="btn-info btn-lg" spinner="startTripOperation" />
                    </div>
                @endif

            @else
                <div class="flex items-center justify-center gap-1.5 shrink-0 pb-4">
                    @foreach([1, 2, 3, 4, 5, 6] as $i)
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-colors
                            {{ $startStep === $i ? 'bg-primary text-white ring-2 ring-primary/30' : ($startStep > $i ? 'bg-success/20 text-success' : 'bg-base-200 text-base-content/30') }}">
                            @if($startStep > $i)
                                <x-mary-icon name="o-check" class="w-4 h-4" />
                            @else
                                {{ $i }}
                            @endif
                        </div>
                        @if($i < 6)
                            <div class="w-3 h-0.5 {{ $startStep > $i ? 'bg-success' : 'bg-base-200' }}"></div>
                        @endif
                    @endforeach
                </div>

                @if($startStep >= 2 && $selectedSysPrimKlic)
                    @php $summaryDoklad = $this->selectedDoklad; @endphp
                    <div class="bg-base-200 rounded-lg px-4 py-3 shrink-0 mb-4 space-y-1">
                        <div class="text-xl font-bold leading-tight truncate">
                            {{ trim($summaryDoklad->MPSProjekt ?? '') ?: '—' }}
                            <span class="text-base font-mono text-gray-500 ml-1">{{ trim($summaryDoklad->KlicDokla ?? '') ?: '—' }}</span>
                        </div>
                        <div class="flex flex-wrap gap-x-5 gap-y-1 text-sm text-gray-500">
                            @if($selectedDokladRadekEntita && $startStep >= 3)
                                @php $summaryRadek = $this->selectedDokladRadky->firstWhere('EntitaRad', $selectedDokladRadekEntita); @endphp
                                <span>Poz. <strong class="text-gray-800">{{ trim($summaryRadek->Pozice ?? '-') }}</strong></span>
                            @endif
                            @if($evPodsestavId && $startStep >= 4)
                                @php $summaryPods = $this->evPodsestav; @endphp
                                <span>Pods. <strong class="text-gray-800">{{ trim($summaryPods->OznaceniPodsestavy ?? $evPodsestavId) }}</strong></span>
                            @endif
                            @if($drawing_number && $startStep >= 5)
                                <span>Výkres <strong class="text-gray-800">{{ $drawing_number }}</strong></span>
                            @endif
                            @if($machine_id && $startStep >= 6)
                                <span>Stroj <strong class="text-gray-800">{{ $machine_id }}</strong></span>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="flex-1 overflow-y-auto min-h-0">
                    @if($startStep === 1 && $this->activeTrips->isNotEmpty())
                        <div class="bg-info/10 border border-info/30 rounded-lg p-4 mb-4">
                            <div class="flex items-center gap-2 mb-2">
                                <x-mary-icon name="o-truck" class="w-5 h-5 text-info" />
                                <span class="text-sm font-bold text-info">Aktivní služební cesty</span>
                            </div>
                            <div class="space-y-2">
                                @foreach($this->activeTrips as $trip)
                                    <button type="button"
                                        wire:click="selectTrip('{{ trim($trip->KlicSluzebniCesty) }}')"
                                        class="w-full text-left border border-info/20 rounded-lg p-3 hover:bg-info/5 transition-colors">
                                        <div class="font-semibold text-sm">{{ trim($trip->Nazev ?? trim($trip->KlicSluzebniCesty)) }}</div>
                                        <div class="flex flex-wrap gap-x-4 gap-y-0.5 text-xs text-gray-500 mt-1">
                                            <span>{{ $trip->DatumACasOd?->format('d.m.') ?? '' }} – {{ $trip->DatumACasDo?->format('d.m.') ?? '' }}</span>
                                            @if(trim($trip->zakaznikSubjekt->Nazev1 ?? ''))
                                                <span>{{ trim($trip->zakaznikSubjekt->Nazev1) }}</span>
                                            @endif
                                            @if($trip->doklad)
                                                <span class="font-mono">{{ trim($trip->doklad->MPSProjekt ?? '') }} {{ trim($trip->doklad->KlicDokla ?? '') }}</span>
                                            @endif
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($startStep === 1)
                        @include('livewire.dashboard.partials.start-step-1')
                    @elseif($startStep === 2)
                        @include('livewire.dashboard.partials.start-step-2')
                    @elseif($startStep === 3)
                        @include('livewire.dashboard.partials.start-step-3')
                    @elseif($startStep === 4)
                        @include('livewire.dashboard.partials.start-step-4')
                    @elseif($startStep === 5)
                        @include('livewire.dashboard.partials.start-step-5')
                    @elseif($startStep === 6)
                        @include('livewire.dashboard.partials.start-step-6')
                    @endif
                </div>

                <div class="shrink-0 flex items-center justify-between gap-3 pt-4 border-t border-base-200 mt-4">
                    @if($startStep > $minStep)
                        <x-mary-button label="Zpět" icon="o-arrow-left" wire:click="prevStartStep" class="btn-lg" />
                    @else
                        <x-mary-button label="Zrušit" @click="$wire.showStartDrawer = false" class="btn-lg" />
                    @endif

                    @if($startStep === 6)
                        <x-mary-button label="Zahájit operaci" icon="o-play" wire:click="startOperation" class="btn-primary btn-lg" spinner="startOperation" />
                    @elseif($startStep === 2)
                        <x-mary-button label="Pokračovat bez řádku" icon-right="o-arrow-right" wire:click="skipRadek" class="btn-outline btn-lg" />
                    @elseif($startStep === 3)
                        <x-mary-button label="Pokračovat bez podsestavy" icon-right="o-arrow-right" wire:click="skipPodsestava" class="btn-outline btn-lg" />
                    @elseif($startStep === 4)
                        @if($drawing_number)
                            <x-mary-button label="Dále" icon-right="o-arrow-right" wire:click="nextStartStep" class="btn-primary btn-lg" />
                        @else
                            <x-mary-button label="Pokračovat bez výkresu" icon-right="o-arrow-right" wire:click="skipDrawingNumber" class="btn-outline btn-lg" />
                        @endif
                    @else
                        <x-mary-button label="Dále" icon-right="o-arrow-right" wire:click="nextStartStep" class="btn-primary btn-lg" />
                    @endif
                </div>

            @endif
        </div>
    </x-mary-drawer>
</div>
