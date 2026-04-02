<div class="flex flex-col h-full">
    @php
        $radky = $this->selectedDokladRadky;
    @endphp

    @if($radky->count() > 5)
        <div class="mb-4 shrink-0">
            <x-mary-input
                placeholder="Filtrovat řádky (pozice, materiál...)"
                wire:model.live.debounce.300ms="radekFilter"
                icon="o-funnel"
                clearable
            />
        </div>
    @endif

    <div class="flex-1 overflow-y-auto pr-2 space-y-2 min-h-0">
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
                        Řádek {{ trim($radek->CisloRadk ?? $entita) }}
                        @if(trim($radek->Pozice ?? ''))
                            <span class="text-gray-500 font-normal ml-2">Poz. {{ trim($radek->Pozice) }}</span>
                        @endif
                    </div>
                </div>

                <div class="text-sm border-t border-base-200 pt-1 mt-1">
                    @if($radek->materialPolozka)
                        <div class="font-semibold text-gray-700 truncate" title="{{ trim($radek->materialPolozka->Nazev1 ?? '') }}">
                            {{ trim($radek->materialPolozka->Nazev1 ?? '') }}
                        </div>
                    @endif
                    <div class="text-gray-500 flex items-center justify-between mt-1">
                        <span>Množství: {{ (float)($radek->Mnozstvi ?? 0) }} {{ trim($radek->MJ ?? 'ks') }}</span>
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
</div>
