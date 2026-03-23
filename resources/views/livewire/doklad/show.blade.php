<div>
    <x-mary-header separator>
        <x-slot:title>
            <div class="flex items-center gap-3">
                <x-mary-button
                    icon="o-arrow-left"
                    class="btn-circle btn-ghost btn-sm"
                    link="{{ route('stadokl.index') }}"
                    tooltip-bottom="Zpět na seznam"
                />

                <span>Detail Dokladu: {{ $staDokl->doklad->KlicDokla ?? 'N/A' }}</span>
            </div>
        </x-slot:title>

        <x-slot:actions>
            <livewire:doklad.print-doklad-label
                :dokladId="$staDokl->doklad->KlicDokla ?? $staDokl->Doklad"
                :doklad="$staDokl->doklad"
            />
        </x-slot:actions>
    </x-mary-header>

    <div class="grid lg:grid-cols-2 gap-5 mb-8">

        <x-mary-card title="Základní informace" shadow separator>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <div class="text-xs text-gray-500 font-bold uppercase">Klíč Dokladu</div>
                    <div>{{ $staDokl->doklad->KlicDokla ?? 'N/A' }}</div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 font-bold uppercase">MPS Projekt</div>
                    <div>{{ $staDokl->doklad->MPSProjekt ?? '-' }}</div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 font-bold uppercase">Termín Datum</div>
                    <div>{{ $staDokl->doklad->TerminDatum ?? '-' }}</div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 font-bold uppercase">Typ Pohybu</div>
                    <div>{{ $staDokl->TypPohybu ?? '-' }}</div>
                </div>

                <div class="col-span-2">
                    <div class="text-xs text-gray-500 font-bold uppercase">Vyhodnocení</div>
                    <div>{{ $staDokl->Vyhodnoceni ?? '-' }}</div>
                </div>

                <div class="col-span-2">
                    <div class="text-xs text-gray-500 font-bold uppercase">SysPrimKlicDokladu</div>
                    <div>{{ $staDokl->doklad->SysPrimKlicDokladu ?? '-' }}</div>
                </div>
            </div>
        </x-mary-card>

        <x-mary-card title="Vazby" shadow separator>
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <div class="text-xs text-gray-500 font-bold uppercase">Vlastní Osoba</div>
                    <div class="text-lg">{{ $staDokl->doklad->vlastniOsoba->Prijmeni ?? '-' }}</div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 font-bold uppercase">Zakázka (Klíč)</div>
                    <div>{{ $staDokl->doklad->rodicZakazka->KlicDokla ?? '-' }}</div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 font-bold uppercase">Specifický Symbol</div>
                    <div class="badge badge-neutral font-mono">
                        {{ $staDokl->doklad->rodicZakazka->SpecifiSy ?? '-' }}
                    </div>
                </div>

            </div>
        </x-mary-card>
    </div>

    <x-mary-card title="Položky dokladu" shadow>

        <x-mary-table :headers="$this->headers()" :rows="$radky" striped>

            @scope('cell_MnozstviZMJ', $radek)
            {{ number_format((float)$radek->MnozstviZMJ, 2) }}
            <span class="text-gray-400 text-xs">{{ $radek->ZaklMerJednotka }}</span>
            @endscope

            @scope('cell_ProdCeZaZMJvM1D', $radek)
            <span class="font-bold">
                    {{ number_format((float)$radek->ProdCeZaZMJvM1D, 2) }}
                </span>
            @endscope

            <x-slot:empty>
                <div class="text-center py-10 text-gray-500">
                    <x-mary-icon name="o-inbox" class="w-12 h-12 mx-auto text-gray-300" />
                    <div class="mt-2">Žádné položky nenalezeny.</div>
                </div>
            </x-slot:empty>

        </x-mary-table>

    </x-mary-card>
</div>
