<div class="space-y-2">
    @php
        $podsestavy = $this->selectedRadekPodsestavy;
    @endphp

    @forelse($podsestavy as $pods)
        @php $isSelected = $evPodsestavId === $pods->ID; @endphp
        <button type="button"
            wire:click="selectPodsestava({{ $pods->ID }})"
            class="w-full text-left border-2 rounded-lg p-3 transition-colors {{ $isSelected ? 'border-primary bg-primary/10' : 'border-base-200 hover:border-primary/30' }}">
            <div class="font-bold font-mono text-base mb-1 {{ $isSelected ? 'text-primary' : '' }}">
                {{ trim($pods->OznaceniPodsestavy ?? '—') }}/{{ trim($pods->CisloPoziceNaVykresu ?? '—') }}
            </div>
            <div class="grid grid-cols-2 gap-x-2 gap-y-1 text-sm text-gray-600 border-t border-base-200 pt-1 mt-1">
                <div>Pozice: <span class="font-semibold text-gray-800">{{ trim($pods->Pozice ?? '—') }}</span></div>
                <div>Množství: <span class="font-semibold text-gray-800">{{ (int)($pods->Mnozstvi ?? 0) }} ks</span></div>
                @if(trim($pods->CisloVykresu ?? ''))
                    <div class="col-span-2">Výkres: <span class="font-semibold text-gray-800">{{ trim($pods->CisloVykresu) }}</span></div>
                @endif
            </div>
        </button>
    @empty
        <div class="text-center border-2 border-dashed border-base-200 rounded-lg text-gray-500 py-8">
            Nenalezeny žádné podsestavy.
        </div>
    @endforelse
</div>
