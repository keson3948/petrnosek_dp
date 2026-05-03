@props(['staDokl', 'mistrUser', 'compact' => false, 'context' => ''])

@if($compact)

    <div class="flex items-center gap-3">
        <div class="shrink-0 w-10 h-10 rounded-full"
             style="background-color: {{ $mistrUser?->color ?? '#6b7280' }}"></div>
        <div class="min-w-0">
            <div class="text-lg font-medium leading-tight">
                {{ trim($staDokl->doklad->KlicDokla ?? '') }} / <span class="text-xl font-extrabold">{{ trim($staDokl->doklad->MPSProjekt ?? '-') }}</span>
            </div>
            <div class="flex flex-wrap items-center gap-x-4 gap-y-0.5 text-xs text-gray-500 mt-0.5">
                <span>{{ trim($staDokl->doklad->vlastniOsoba->Prijmeni ?? '-') }}@if($mistrUser?->cislo_mistra) #{{ $mistrUser->cislo_mistra }}@endif</span>
                <span>{{ $staDokl->doklad->DatVystav ? \Carbon\Carbon::parse($staDokl->doklad->DatVystav)->format('d.m.Y') : '' }}</span>
                @if(trim($staDokl->doklad->rodicZakazka->KlicDokla ?? ''))
                    <span>OZ: {{ trim($staDokl->doklad->rodicZakazka->KlicDokla) }}</span>
                @endif
                @if(trim($staDokl->doklad->rodicZakazka->SpecifiSy ?? ''))
                    <span class="font-mono">{{ trim($staDokl->doklad->rodicZakazka->SpecifiSy) }}</span>
                @endif
            </div>
            @if($context)
                <div class="mt-2 flex items-center gap-2">
                    <span class="text-sm font-semibold">{{ $context }}</span>
                </div>
            @endif
        </div>
    </div>
    

@else

    <div class="flex flex-col sm:grid sm:grid-cols-2 gap-4 lg:gap-6">

        <div class="flex items-center sm:items-start gap-3 sm:gap-5">
            <div
                class="shrink-0 w-10 h-10 sm:w-16 sm:h-16 rounded-full"
                style="background-color: {{ $mistrUser?->color ?? '#6b7280' }}"
            ></div>
            <div class="min-w-0">
                <div class="text-xs text-gray-400 font-bold uppercase tracking-wide">Výrobní příkaz č.</div>
                <div class="text-xl sm:text-3xl font-medium leading-tight truncate">
                    {{ trim($staDokl->doklad->KlicDokla ?? '') }} / <span
                        class="text-2xl sm:text-4xl font-extrabold">{{ trim($staDokl->doklad->MPSProjekt ?? '-') }}</span>
                </div>
                <div class="mt-1 sm:mt-2 grid grid-cols-[auto_1fr] items-baseline gap-x-2 sm:gap-x-3 gap-y-1">
                    <span class="text-xs text-gray-400 uppercase font-bold w-12 sm:w-14">Mistr</span>
                    <span class="font-semibold text-sm sm:text-base">
                        {{ trim($staDokl->doklad->vlastniOsoba->Prijmeni ?? '-') }}
                        @if($mistrUser?->cislo_mistra)
                            <span class="text-xs text-gray-400 font-normal ml-1">#{{ $mistrUser->cislo_mistra }}</span>
                        @endif
                    </span>
                    @if(trim($staDokl->doklad->rodicZakazka?->vlastniOsoba?->Prijmeni ?? '') !== '')
                        <span class="text-xs text-gray-400 uppercase font-bold w-12 sm:w-14">Garant</span>
                        <span class="font-semibold text-sm sm:text-base">{{ trim($staDokl->doklad->rodicZakazka->vlastniOsoba->Prijmeni) }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-2 gap-x-4 gap-y-2 sm:gap-x-6 sm:gap-y-3 content-start">
            <div>
                <div class="text-xs text-gray-400 font-bold uppercase tracking-wide">Vystaveno</div>
                <div class="text-sm sm:text-lg font-semibold">
                    {{ $staDokl->doklad->DatVystav ? \Carbon\Carbon::parse($staDokl->doklad->DatVystav)->format('d.m.Y') : '-' }}
                </div>
            </div>
            <div>
                <div class="text-xs text-gray-400 font-bold uppercase tracking-wide">Cena VP</div>
                <div class="text-sm sm:text-lg font-semibold">
                    {{ $staDokl->doklad->ZaklDanCelkVM1D !== null ? number_format((float) $staDokl->doklad->ZaklDanCelkVM1D, 2, ',', ' ') . ' Kč' : '-' }}
                </div>
            </div>
            <div>
                <div class="text-xs text-gray-400 font-bold uppercase tracking-wide">Obch. zakázka</div>
                <div class="text-sm sm:text-lg font-semibold">{{ trim($staDokl->doklad->rodicZakazka->KlicDokla ?? '-') }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-400 font-bold uppercase tracking-wide">Hmotnost</div>
                <div class="text-sm sm:text-lg font-semibold">
                    {{ $staDokl->doklad->NettoKg && (float) $staDokl->doklad->NettoKg > 0 ? number_format((float) $staDokl->doklad->NettoKg, 2, ',', ' ') . ' kg' : '-' }}
                </div>
            </div>
            <div>
                <div class="text-xs text-gray-400 font-bold uppercase tracking-wide">Int. projekt</div>
                <div class="text-sm sm:text-lg font-semibold font-mono">{{ trim($staDokl->doklad->rodicZakazka->SpecifiSy ?? '-') }}</div>
            </div>
        </div>

    </div>

@endif
