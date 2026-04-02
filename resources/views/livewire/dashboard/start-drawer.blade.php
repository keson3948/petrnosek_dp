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
        class="w-full lg:w-[480px]"
    >
        <div class="flex flex-col h-full gap-4">
            {{-- Step progress indicator --}}
            <div class="flex items-center justify-center gap-1.5 shrink-0">
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
            @if($startStep >= 2)
                <div class="bg-base-200 rounded-lg px-3 py-2 space-y-1 text-sm shrink-0">
                    @if($selectedDokladKey)
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500 shrink-0">VP:</span>
                            <span class="font-bold font-mono truncate">{{ $selectedDokladKey }}</span>
                        </div>
                    @endif
                    @if($selectedDokladRadekEntita && $startStep >= 3)
                        @php $summaryRadek = $this->selectedDokladRadky->firstWhere('EntitaRad', $selectedDokladRadekEntita); @endphp
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500 shrink-0">Řádek:</span>
                            <span class="font-semibold">
                                {{ trim($summaryRadek->CisloRadk ?? $selectedDokladRadekEntita) }}
                                @if($summaryRadek && trim($summaryRadek->Pozice ?? ''))
                                    <span class="text-gray-400 font-normal ml-1">Poz. {{ trim($summaryRadek->Pozice) }}</span>
                                @endif
                            </span>
                        </div>
                    @endif
                    @if($evPodsestavId && $startStep >= 4)
                        @php $summaryPods = $this->evPodsestav; @endphp
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500 shrink-0">Podsestava:</span>
                            <span class="font-semibold font-mono">{{ trim($summaryPods->OznaceniPodsestavy ?? $evPodsestavId) }}</span>
                        </div>
                    @endif
                    @if($drawing_number && $startStep >= 5)
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500 shrink-0">Výkres:</span>
                            <span class="font-semibold">{{ $drawing_number }}</span>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Step content (flexibilní výška pro scroll) --}}
            <div class="flex-1 overflow-hidden">
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
        </div>

        <x-slot:actions>
            @if($startStep > 1)
                <x-mary-button label="Zpět" icon="o-arrow-left" wire:click="prevStartStep" class="btn-lg" />
            @else
                <x-mary-button label="Zrušit" @click="$wire.showStartDrawer = false" class="btn-lg" />
            @endif

            @if($startStep === 5)
                <x-mary-button label="Zahájit operaci" icon="o-play" wire:click="startOperation" class="btn-primary btn-lg" spinner="startOperation" />
            @else
                <x-mary-button label="Dále" icon-right="o-arrow-right" wire:click="nextStartStep" class="btn-primary btn-lg" />
            @endif
        </x-slot:actions>
    </x-mary-drawer>
</div>
