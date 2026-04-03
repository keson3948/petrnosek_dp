<div class="space-y-2">
    @php
        $radky = $this->selectedDokladRadky;
    @endphp

    @forelse($radky as $radek)
        @php
            $entita = $radek->EntitaRad;
            $isSelected = $selectedDokladRadekEntita === $entita;
            $podsCount = $radek->evPodsestavy->count();
        @endphp
        <button type="button"
            wire:click="selectRadek({{ $entita }})"
            class="w-full text-left border-2 rounded-lg p-3 transition-colors {{ $isSelected ? 'border-primary bg-primary/10' : 'border-base-200 hover:border-primary/30' }}">
            <div class="flex justify-between items-start mb-1">
                <div class="font-bold text-base {{ $isSelected ? 'text-primary' : '' }}">
                    Poz. {{ trim($radek->Pozice) }}
                </div>
            </div>

            <div class="text-sm border-t border-base-200 pt-1 mt-1">
                @if($radek->materialPolozka)
                    <div class="font-semibold text-gray-700 truncate" title="{{ trim($radek->materialPolozka->Nazev1 ?? '') }}">
                        {{ trim($radek->materialPolozka->Nazev1 ?? '') }}
                    </div>
                @endif
                <div class="text-gray-500 flex items-center justify-between mt-1">
                    <span>Množství: {{ (float)($radek->MnozstviZMJ ?? 0) }} ks</span>
                    @if($podsCount > 0)
                        <span class="badge badge-info badge-sm text-xs">{{ $podsCount }} podsestav</span>
                    @else
                        <span class="badge badge-ghost badge-sm text-xs opacity-60">bez podsestavy</span>
                    @endif
                </div>
            </div>
        </button>
    @empty
        <div class="text-center border-2 border-dashed border-base-200 rounded-lg text-gray-500 py-8">
            VP neobsahuje žádné zpracovatelné řádky.
        </div>
    @endforelse
</div>
