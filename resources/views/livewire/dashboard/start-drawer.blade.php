<div>
    <x-mary-drawer
        wire:model="showStartDrawer"
        right
        :title="match($startStep) {
            1 => 'Vyberte výrobní příkaz',
            2 => 'Vyberte řádek VP',
            3 => 'Vyberte podsestavu',
            4 => 'Číslo výkresu',
            5 => 'Stroj a operace',
            default => 'Nová operace',
        }"
        :subtitle="'Krok ' . $startStep"
        separator
        with-close-button
        close-on-escape
        class="w-full lg:w-1/3"
    >
        <div class="flex flex-col h-[calc(100vh-10rem)]">
            {{-- Step progress indicator --}}
            <div class="flex items-center justify-center gap-1.5 shrink-0 pb-4">
                @foreach([1, 2, 3, 4, 5] as $i)
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-colors
                        {{ $startStep === $i ? 'bg-primary text-white ring-2 ring-primary/30' : ($startStep > $i ? 'bg-success/20 text-success' : 'bg-base-200 text-base-content/30') }}">
                        @if($startStep > $i)
                            <x-mary-icon name="o-check" class="w-4 h-4" />
                        @else
                            {{ $i }}
                        @endif
                    </div>
                    @if($i < 5)
                        <div class="w-4 h-0.5 {{ $startStep > $i ? 'bg-success' : 'bg-base-200' }}"></div>
                    @endif
                @endforeach
            </div>

            {{-- Summary breadcrumb --}}
            @if($startStep >= 2 && $selectedDokladKey)
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
                    </div>
                </div>
            @endif

            {{-- Step content (scrollovatelný obsah) --}}
            <div class="flex-1 overflow-y-auto min-h-0">
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
                @endif
            </div>

            {{-- Fixed footer --}}
            <div class="shrink-0 flex items-center justify-between gap-3 pt-4 border-t border-base-200 mt-4">
                @if($startStep > $minStep)
                    <x-mary-button label="Zpět" icon="o-arrow-left" wire:click="prevStartStep" class="btn-lg" />
                @else
                    <x-mary-button label="Zrušit" @click="$wire.showStartDrawer = false" class="btn-lg" />
                @endif

                @if($startStep === 5)
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
        </div>
    </x-mary-drawer>
</div>
