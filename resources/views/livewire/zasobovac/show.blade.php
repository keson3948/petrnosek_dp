<div>
    <x-mary-header separator>
        <x-slot:title>
            <div class="flex items-center gap-3">
                <x-mary-button
                    icon="o-arrow-left"
                    class="btn-circle btn-ghost btn-sm"
                    link="{{ route('zasobovac.index') }}"
                    tooltip-bottom="Zpět na seznam"
                />
                <span>Výrobní příkaz: {{ $staDokl->doklad->KlicDokla ?? 'N/A' }}</span>
            </div>
        </x-slot:title>
        <x-slot:actions>
            @can('can print')
                <x-mary-button
                    icon="o-qr-code"
                    class="btn-square"
                    wire:click="openPrintModal('doklad')"
                    tooltip-bottom="Tisk QR kódu VP"
                />
            @endcan
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card shadow class="mb-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <div class="flex items-start gap-5">
                <div
                    class="shrink-0 w-16 h-16 rounded-full"
                    style="background-color: {{ $mistrUser?->color ?? '#6b7280' }}"
                ></div>
                <div>
                    <div class="text-xs text-gray-400 font-bold uppercase tracking-wide">Výrobní příkaz č.</div>
                    <div class="text-3xl font-medium leading-tight">
                        {{ trim($staDokl->doklad->KlicDokla ?? '') }} / <span class="text-4xl font-extrabold">{{ trim($staDokl->doklad->MPSProjekt ?? '-') }}</span>
                    </div>
                    <div class="mt-2 grid grid-cols-[auto_1fr] items-baseline gap-x-3 gap-y-1">
                        <span class="text-xs text-gray-400 uppercase font-bold w-14">Mistr</span>
                        <span class="font-semibold">
                            {{ trim($staDokl->doklad->vlastniOsoba->Prijmeni ?? '-') }}
                            @if($mistrUser?->cislo_mistra)
                                <span class="text-xs text-gray-400 font-normal ml-1">#{{ $mistrUser->cislo_mistra }}</span>
                            @endif
                        </span>
                        @if(trim($staDokl->doklad->rodicZakazka?->vlastniOsoba?->Prijmeni ?? '') !== '')
                            <span class="text-xs text-gray-400 uppercase font-bold w-14">Garant</span>
                            <span class="font-semibold">{{ trim($staDokl->doklad->rodicZakazka->vlastniOsoba->Prijmeni) }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-x-6 gap-y-3 content-start">
                <div>
                    <div class="text-xs text-gray-400 font-bold uppercase tracking-wide">Vystaveno</div>
                    <div class="text-lg font-semibold">
                        {{ $staDokl->doklad->DatVystav ? \Carbon\Carbon::parse($staDokl->doklad->DatVystav)->format('d.m.Y') : '-' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs text-gray-400 font-bold uppercase tracking-wide">Cena VP</div>
                    <div class="text-lg font-semibold">
                        {{ $staDokl->doklad->ZaklDanCelkVM1D !== null ? number_format((float) $staDokl->doklad->ZaklDanCelkVM1D, 2, ',', ' ') . ' Kč' : '-' }}
                    </div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 font-bold uppercase tracking-wide">Obch. zakázka</div>
                    <div class="text-lg font-semibold">{{ trim($staDokl->doklad->rodicZakazka->KlicDokla ?? '-') }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 font-bold uppercase tracking-wide">Hmotnost</div>
                    <div class="text-lg font-semibold">
                        {{ $staDokl->doklad->NettoKg && (float) $staDokl->doklad->NettoKg > 0 ? number_format((float) $staDokl->doklad->NettoKg, 2, ',', ' ') . ' kg' : '-' }}
                    </div>
                </div>

                  <div>
                    <div class="text-xs text-gray-400 font-bold uppercase tracking-wide">Int. projekt</div>
                    <div class="text-lg font-semibold font-mono">{{ trim($staDokl->doklad->rodicZakazka->SpecifiSy ?? '-') }}</div>
                </div>
            </div>

        </div>
    </x-mary-card>


    @if(trim($staDokl->doklad->InterniPoznamka ?? '') !== '')
        <x-mary-card shadow class="mb-8">
            <div class="flex items-start gap-3">
                <x-mary-icon name="o-chat-bubble-left-ellipsis" class="w-5 h-5 text-base-content/40 shrink-0 mt-0.5" />
                <div>
                    <div class="text-xs text-gray-400 font-bold uppercase tracking-wide mb-1">Poznámka</div>
                    <div class="text-sm whitespace-pre">{{ trim($staDokl->doklad->InterniPoznamka) }}</div>
                </div>
            </div>
        </x-mary-card>
    @endif

    <x-mary-card title="Položky dokladu" shadow>
        @forelse($radky as $index => $radek)
            <div class="mb-3 flex items-start gap-2">
            @can('can print')
                <x-mary-button
                    type="button"
                    wire:click="openPrintModal('radek', {{ $radek->EntitaRad }})"
                    class="btn-sm btn-square"
                    title="Tisk QR řádku"
                    tooltip="Tisk QR řádku"
                    icon="o-qr-code"
                >
                </x-mary-button>
            @endcan
            <x-mary-collapse class="flex-1 border border-base-200 bg-white rounded-lg">
                <x-slot:heading class="bg-primary/5">
                    @php
                        $datum = $radek->TermiDoda ?? $radek->TerminDatum ?? null;
                    @endphp
                    <div class="w-full -mx-4 -my-2">

                        <div class="bg-primary/5 px-4 py-2 grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-x-4 gap-y-1">
                            <div class="flex gap-2">
                                <div>
                                    <div class="text-[10px] text-gray-400 uppercase font-bold">ŘÁDEK VP</div>
                                    <div class="font-mono text-sm font-bold text-primary">{{ $radek->CisloRadk }}
                                        @if($radek->evPodsestavy->count() > 0)
                                            <span class="badge badge-primary badge-sm mb-0.5">{{ $radek->evPodsestavy->count() }} záz.</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div class="text-[10px] text-gray-400 uppercase font-bold">Položka</div>
                                <div class="font-mono text-sm">{{ trim($radek->Polozka ?? '-') }}</div>
                            </div>
                            <div>
                                <div class="text-[10px] text-gray-400 uppercase font-bold">Termín</div>
                                <div class="text-sm">{{ $datum ? \Carbon\Carbon::parse($datum)->format('d.m.Y') : '-' }}</div>
                            </div>
                            <div class="lg:col-span-2">
                                <div class="text-[10px] text-gray-400 uppercase font-bold">Název</div>
                                <div class="text-sm font-semibold truncate">{{ $radek->TxtRadku2 ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="text-[10px] text-gray-400 uppercase font-bold">Množství</div>
                                <div class="text-sm font-bold">{{ number_format((float)($radek->MnozstviZMJ ?? 0), 0) }} <span class="text-gray-400 font-normal">ks</span></div>
                            </div>
                            <div>
                                <div class="text-[10px] text-gray-400 uppercase font-bold">Materiál</div>
                                <div class="text-sm truncate">{{ trim($radek->materialPolozka->Nazev1 ?? '-') }}</div>
                            </div>
                            <div>
                                <div class="text-[10px] text-gray-400 uppercase font-bold">Povrch. úprava</div>
                                <div class="text-sm truncate">{{ trim($radek->povrchoUpPolozka->Nazev1 ?? '-') }}</div>
                            </div>
                        </div>

                        <div class="px-4 py-2 grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-x-4 gap-y-1">
                            <div class="lg:col-span-3">
                                <div class="text-[10px] text-gray-400 uppercase font-bold">Text řádku</div>
                                <div class="text-xs text-gray-600">{{ $radek->TxtRadku ? preg_replace('/[\r\n]+/', '; ', trim($radek->TxtRadku)) : '-' }}</div>
                            </div>
                            <div>
                                <div class="text-[10px] text-gray-400 uppercase font-bold">Kontrakt</div>
                                <div class="text-xs text-gray-600">{{ $radek->Kontrakt ?? '-' }}</div>
                            </div>
                            <div class="lg:col-span-2">
                                <div class="text-[10px] text-gray-400 uppercase font-bold">ObjeHospS</div>
                                <div class="text-xs text-gray-600">{{ $radek->ObjeHospS ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="text-[10px] text-gray-400 uppercase font-bold">Pozice</div>
                                <div class="text-xs text-gray-600">{{ $radek->Pozice ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="text-[10px] text-gray-400 uppercase font-bold">Cena/ks</div>
                                <div class="text-xs text-gray-600">{{ number_format((float)($radek->PCZaZMJVCM ?? 0), 2) }}</div>
                            </div>
                        </div>

                        @if($radek->RozsahPoz || $radek->Poznamka)
                            <div class="px-4 py-2 border-t border-gray-100 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-8 gap-x-4 gap-y-1">
                                @if($radek->RozsahPoz)
                                    <div class="lg:col-span-4">
                                        <div class="text-[10px] text-gray-400 uppercase font-bold">Rozsah pozic</div>
                                        <div class="text-xs text-gray-500 italic">{{ preg_replace('/[\r\n]+/', '; ', trim($radek->RozsahPoz)) }}</div>
                                    </div>
                                @endif
                                @if($radek->Poznamka)
                                    <div class="lg:col-span-4">
                                        <div class="text-[10px] text-gray-400 uppercase font-bold">Poznámka</div>
                                        <div class="text-xs text-gray-500 italic">{{ preg_replace('/[\r\n]+/', '; ', trim($radek->Poznamka)) }}</div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </x-slot:heading>
                <x-slot:content>
                    <div class="space-y-3 pt-3"
                         x-init="
                            const cb = $el.closest('.collapse')?.querySelector('input[type=checkbox], input[type=radio]');
                            if (cb) cb.addEventListener('change', () => {
                                if (cb.checked) setTimeout(() => $el.querySelector('input')?.focus(), 150);
                            });
                         ">
                        @foreach($radek->evPodsestavy as $index_ev => $ev)
                            @if($editingId === $ev->ID)
                                <div class="p-3 rounded-lg bg-warning/5 border border-warning/30">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                        <x-mary-input
                                            label="Číslo výkresu *"
                                            wire:model="editEntry.CisloVykresu"
                                            placeholder="Zadejte číslo výkresu"
                                        />
                                        <x-mary-input
                                            label="Množství *"
                                            type="number"
                                            step="0.01"
                                            wire:model="editEntry.Mnozstvi"
                                            placeholder="0.00"
                                        />
                                        <x-mary-input
                                            label="Poznámka"
                                            wire:model="editEntry.Poznamka"
                                            placeholder="Volitelné"
                                        />
                                    </div>
                                    <div class="mt-3 flex justify-end gap-2">
                                        <x-mary-button
                                            label="Zrušit"
                                            icon="o-x-mark"
                                            class="btn-ghost btn-sm"
                                            wire:click="cancelEdit"
                                        />
                                        <x-mary-button
                                            label="Uložit změny"
                                            icon="o-check"
                                            class="btn-warning btn-sm"
                                            wire:click="updateEntry"
                                            spinner="updateEntry"
                                        />
                                    </div>
                                </div>
                            @else
                                <div class="flex items-center justify-between p-3 rounded-lg bg-base-100 border border-base-200">
                                    <div class="flex items-center gap-6">
                                        <div>
                                            <div class="text-xs text-gray-400 uppercase">Č. PODSESTAVY</div>
                                            <div class="font-semibold">{{ trim($ev->OznaceniPodsestavy ?? $radek->CisloRadk . '.' . ($index_ev + 1)) }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-400 uppercase">Číslo výkresu</div>
                                            <div class="font-semibold">{{ $ev->CisloVykresu }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-400 uppercase">Množství</div>
                                            <div class="font-semibold">{{ $ev->Mnozstvi }}</div>
                                        </div>
                                        @if($ev->Poznamka)
                                            <div>
                                                <div class="text-xs text-gray-400 uppercase">Poznámka</div>
                                                <div class="text-sm text-gray-600">{{ $ev->Poznamka }}</div>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @can('can print')
                                            <x-mary-button
                                                icon="o-qr-code"
                                                class="btn-sm btn-ghost"
                                                wire:click="printPodsestava({{ $ev->ID }})"
                                                spinner="printPodsestava({{ $ev->ID }})"
                                                tooltip="Tisk QR štítku podsestavy"
                                            />
                                        @endcan
                                        <x-mary-button
                                            icon="o-pencil"
                                            class="btn-sm btn-ghost"
                                            wire:click="startEdit({{ $ev->ID }})"
                                            tooltip="Upravit"
                                        />
                                        <x-mary-button
                                            icon="o-trash"
                                            class="btn-sm btn-ghost text-error"
                                            wire:click="deleteEntry({{ $ev->ID }})"
                                            wire:confirm="Opravdu smazat tento záznam?"
                                            tooltip="Smazat"
                                        />
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        <x-mary-card title="Přidat nový záznam" class="p-2 bg-primary/5 border border-primary/20">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3"
                                 @keydown.enter.prevent="$wire.saveEntry({{ $index }})"
                                 @entry-saved.window="if ($event.detail.rowIndex === {{ $index }}) $nextTick(() => $el.querySelector('input')?.focus())"
                            >
                                <x-mary-input
                                    label="Číslo výkresu *"
                                    wire:model="newEntries.{{ $index }}.CisloVykresu"
                                    placeholder="Zadejte číslo výkresu"
                                />
                                <x-mary-input
                                    label="Množství *"
                                    type="number"
                                    step="1"
                                    wire:model="newEntries.{{ $index }}.Mnozstvi"
                                    placeholder="0.00"
                                />
                                <x-mary-input
                                    label="Poznámka"
                                    wire:model="newEntries.{{ $index }}.Poznamka"
                                    placeholder="Volitelné"
                                />
                            </div>
                            <x-slot:actions>
                                <x-mary-button
                                    label="Uložit"
                                    icon="o-plus"
                                    class="btn-primary btn-sm"
                                    wire:click="saveEntry({{ $index }})"
                                    spinner="saveEntry({{ $index }})"
                                />
                            </x-slot:actions>
                        </x-mary-card>
                    </div>
                </x-slot:content>
            </x-mary-collapse>
            </div>
        @empty
            <div class="text-center py-10 text-gray-500">
                <x-mary-icon name="o-inbox" class="w-12 h-12 mx-auto text-gray-300" />
                <div class="mt-2">Žádné položky nenalezeny.</div>
            </div>
        @endforelse
    </x-mary-card>

    {{-- Print modal --}}
    <x-mary-modal wire:model="showPrintModal" title="Tisk QR štítku" separator>
        <div class="space-y-4">
            <div class="text-sm text-gray-500">
                @if($printType === 'doklad')
                    Tisk QR kódu celého výrobního příkazu <span class="font-bold">{{ $staDokl->doklad->KlicDokla }}</span>
                @elseif($printType === 'radek')
                    Tisk QR kódu řádku VP
                @elseif($printType === 'podsestava')
                    Tisk QR kódu podsestavy
                @endif
            </div>

            <x-mary-input
                label="Počet kusů"
                type="number"
                wire:model="printCopies"
                min="1"
                max="100"
                class="input-lg"
                autofocus
            />
        </div>

        <x-slot:actions>
            <x-mary-button label="Zrušit" @click="$wire.showPrintModal = false" />
            <x-mary-button
                label="Tisknout"
                icon="o-printer"
                class="btn-primary"
                wire:click="confirmPrint"
                spinner="confirmPrint"
            />
        </x-slot:actions>
    </x-mary-modal>

</div>
