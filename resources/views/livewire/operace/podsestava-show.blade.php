<div>
    <x-mary-header title="Detail podsestavy" separator>
        <x-slot:actions>
            <x-mary-button label="Zpět" icon="o-arrow-left" link="{{ url()->previous() }}" class="btn-ghost" />
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @if($doklad)
                <div>
                    <div class="text-sm text-gray-500">MPS Projekt</div>
                    <div class="text-lg font-bold">{{ trim($doklad->MPSProjekt ?? '-') }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Výrobní příkaz</div>
                    <div class="text-lg font-bold">{{ trim($doklad->KlicDokla ?? '-') }}</div>
                </div>
            @endif

            <div>
                <div class="text-sm text-gray-500">Č. podsestavy</div>
                <div class="text-lg font-bold">{{ trim($evPods->OznaceniPodsestavy ?? '-') }}</div>
            </div>

            <div>
                <div class="text-sm text-gray-500">Pozice</div>
                <div class="text-lg font-bold">{{ trim($evPods->Pozice ?? '-') }}</div>
            </div>

            <div>
                <div class="text-sm text-gray-500">Číslo výkresu</div>
                <div class="text-lg font-bold">{{ trim($evPods->CisloVykresu ?? '-') }}</div>
            </div>

            <div>
                <div class="text-sm text-gray-500">Množství</div>
                <div class="text-lg font-bold">{{ (int) ($evPods->Mnozstvi ?? 0) }} ks</div>
            </div>

            @if($mistrUser)
                <div>
                    <div class="text-sm text-gray-500">Mistr</div>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-lg font-bold">{{ $mistrUser->name }}</span>
                        @if($mistrUser->cislo_mistra)
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full border-2 border-black font-bold text-sm">
                                {{ $mistrUser->cislo_mistra }}
                            </span>
                        @endif
                    </div>
                </div>
            @endif

            @if(trim($evPods->Poznamka ?? ''))
                <div class="md:col-span-2">
                    <div class="text-sm text-gray-500">Poznámka</div>
                    <div class="text-base">{{ trim($evPods->Poznamka) }}</div>
                </div>
            @endif
        </div>
    </x-mary-card>
</div>
