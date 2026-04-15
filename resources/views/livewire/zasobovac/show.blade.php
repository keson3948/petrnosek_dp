<div>
    <x-mary-header separator>
        <x-slot:title>
            <div class="flex items-center gap-3">
                <x-mary-button
                    icon="o-arrow-left"
                    class="btn-circle btn-ghost btn-sm"
                    link="{{ $backRoute ?? route('zasobovac.index') }}"
                    tooltip-bottom="Zpět"
                />
                <span>Výrobní příkaz: {{ $staDokl->doklad->KlicDokla ?? 'N/A' }}</span>
            </div>
        </x-slot:title>
        <x-slot:actions>
            @can('manage zasobovani')
                @can('can print')
                    <x-mary-button
                        icon="o-qr-code"
                        class="btn-square text-primary"
                        wire:click="openPrintModal('doklad')"
                        tooltip-bottom="Tisk QR kódu VP"
                    />
                @endcan
            @endcan
            <x-mary-button
                wire:click="openHistory('vp', null, '{{ trim($staDokl->doklad->MPSProjekt ?? '') }} {{ trim($staDokl->doklad->KlicDokla ?? '') }}')"
                icon="o-clock"
                class="btn-square {{ $vpHasHistory ? 'text-primary' : 'text-base-content/20' }}"
                tooltip-bottom="{{ $vpHasHistory ? 'Práce na VP' : 'Žádná historie prací' }}"
                :disabled="!$vpHasHistory"
            />
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card class="mb-8">
        @include('livewire.zasobovac.partials.vp-header', ['staDokl' => $staDokl, 'mistrUser' => $mistrUser])
    </x-mary-card>


    @if(trim($staDokl->doklad->InterniPoznamka ?? '') !== '')
        <x-mary-card class="mb-8">
            <div class="flex items-start gap-3">
                <x-mary-icon name="o-chat-bubble-left-ellipsis" class="w-5 h-5 text-base-content/40 shrink-0 mt-0.5"/>
                <div>
                    <div class="text-xs text-gray-400 font-bold uppercase tracking-wide mb-1">Poznámka</div>
                    <div class="text-sm whitespace-pre-wrap break-words">{{ trim($staDokl->doklad->InterniPoznamka) }}</div>
                </div>
            </div>
        </x-mary-card>
    @endif

    <x-mary-card title="Položky dokladu">
        @forelse($radky as $index => $radek)
            <div class="mb-3 flex items-start gap-2 ">
                <x-mary-collapse class="flex-1 border border-base-200 bg-white rounded-lg">
                    <x-slot:heading class="bg-primary/5 pt-0">
                        @php
                            $datum = $radek->TermiDoda ?? $radek->TerminDatum ?? null;
                        @endphp
                        <div class="w-full -mx-4 -my-2">

                            <div
                                class="bg-primary/5 rounded-r-lg  px-4 py-4 grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-x-4 gap-y-1">
                                <div class="flex gap-2">
                                    <div>
                                        <div class="text-[10px] text-gray-400 uppercase font-bold">ŘÁDEK VP</div>
                                        <div class="font-mono text-sm font-bold text-primary">{{ $radek->CisloRadk }}
                                            @if($radek->evPodsestavy->count() > 0)
                                                <x-mary-badge class="badge-primary badge-soft bg-gray-400 text-white "
                                                              value="{{ $radek->evPodsestavy->count() }}"/>
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
                                    <div
                                        class="text-sm">{{ $datum ? \Carbon\Carbon::parse($datum)->format('d.m.Y') : '-' }}</div>
                                </div>
                                <div class="lg:col-span-2">
                                    <div class="text-[10px] text-gray-400 uppercase font-bold">Název</div>
                                    <div class="text-sm font-semibold truncate">{{ $radek->TxtRadku2 ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-[10px] text-gray-400 uppercase font-bold">Množství</div>
                                    <div
                                        class="text-sm font-bold">{{ number_format((float)($radek->MnozstviZMJ ?? 0), 0) }}
                                        <span class="text-gray-400 font-normal">ks</span></div>
                                </div>
                                <div>
                                    <div class="text-[10px] text-gray-400 uppercase font-bold">Materiál</div>
                                    <div
                                        class="text-sm truncate">{{ trim($radek->materialPolozka->Nazev1 ?? '-') }}</div>
                                </div>
                                <div>
                                    <div class="text-[10px] text-gray-400 uppercase font-bold">Povrch. úprava</div>
                                    <div
                                        class="text-sm truncate">{{ trim($radek->povrchoUpPolozka->Nazev1 ?? '-') }}</div>
                                </div>
                            </div>

                            <div class="px-4 py-2 grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-x-4 gap-y-1">
                                <div class="lg:col-span-3">
                                    <div class="text-[10px] text-gray-400 uppercase font-bold">Text řádku</div>
                                    <div
                                        class="text-xs text-gray-600">{{ $radek->TxtRadku ? preg_replace('/[\r\n]+/', '; ', trim($radek->TxtRadku)) : '-' }}</div>
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
                                    <div
                                        class="text-xs text-gray-600">{{ number_format((float)($radek->PCZaZMJVCM ?? 0), 2) }}</div>
                                </div>
                            </div>

                            <div
                                class="pl-4 py-2 border-t border-gray-100 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-8 gap-x-4 gap-y-1 items-center">
                                <div class="lg:col-span-7">
                                    @if($radek->RozsahPoz || $radek->Poznamka)
                                        <div class="flex gap-4">
                                            @if($radek->RozsahPoz)
                                                <div>
                                                    <div class="text-[10px] text-gray-400 uppercase font-bold">Rozsah
                                                        pozic
                                                    </div>
                                                    <div
                                                        class="text-xs text-gray-500 italic">{{ preg_replace('/[\r\n]+/', '; ', trim($radek->RozsahPoz)) }}</div>
                                                </div>
                                            @endif
                                            @if($radek->Poznamka)
                                                <div>
                                                    <div class="text-[10px] text-gray-400 uppercase font-bold">
                                                        Poznámka
                                                    </div>
                                                    <div
                                                        class="text-xs text-gray-500 italic">{{ preg_replace('/[\r\n]+/', '; ', trim($radek->Poznamka)) }}</div>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                <div class="flex justify-end space-x-1 relative z-10">
                                    @can('manage zasobovani')

                                        @can('can print')

                                            <x-mary-button
                                                type="button"
                                                wire:click="openPrintModal('radek', {{ $radek->EntitaRad }})"
                                                class="btn-sm btn-square text-primary"
                                                title="Tisk QR řádku"
                                                tooltip-left="Tisk QR řádku"
                                                icon="o-qr-code"
                                            >
                                            </x-mary-button>

                                        @endcan

                                    @endcan
                                    @php $radekHas = ($radekHasHistory[$radek->EntitaRad] ?? false); @endphp
                                    <x-mary-button
                                        wire:click="openHistory('radek', {{ $radek->EntitaRad }}, '{{ trim($staDokl->doklad->MPSProjekt ?? '') }} {{ trim($staDokl->doklad->KlicDokla ?? '') }}', 'ŘÁDEK VP: {{ $radek->CisloRadk }} | POZICE: {{ trim($radek->Pozice ?? '-') }}')"
                                        icon="o-clock"
                                        class="btn-sm btn-square {{ $radekHas ? 'text-primary' : 'text-base-content/20' }}"
                                        tooltip="{{ $radekHas ? 'Práce na řádku' : 'Žádná historie prací' }}"
                                        :disabled="!$radekHas"
                                    />
                                </div>
                            </div>

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
                                    @can('manage zasobovani')
                                        <div class="p-3 rounded-xl bg-warning/5 border border-warning/30">
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                                <x-mary-input
                                                    label="Číslo výkresu *"
                                                    wire:model="editEntry.CisloVykresu"
                                                    placeholder="Zadejte číslo výkresu"
                                                />
                                                <x-mary-input
                                                    label="Množství *"
                                                    type="number"
                                                    step="1"
                                                    wire:model="editEntry.Mnozstvi"
                                                    placeholder="0"
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
                                    @endcan
                                @else
                                    <div
                                        class="flex items-center justify-between p-3 rounded-lg bg-base-100 border border-base-200">
                                        <div class="flex items-center gap-6">
                                            <div>
                                                <div class="text-xs text-gray-400 uppercase">Č. PODSESTAVY</div>
                                                <div
                                                    class="font-semibold">{{ trim($ev->OznaceniPodsestavy ?? $radek->CisloRadk . '.' . ($index_ev + 1)) }}</div>
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
                                        @can('manage zasobovani')
                                            <div class="flex items-center gap-2">
                                                @can('can print')
                                                    <x-mary-button
                                                        icon="o-qr-code"
                                                        class="btn-sm btn-ghost text-primary"
                                                        wire:click="printPodsestava({{ $ev->ID }})"
                                                        spinner="printPodsestava({{ $ev->ID }})"
                                                        tooltip="Tisk QR štítku podsestavy"
                                                    />
                                                @endcan
                                                @endcan
                                                @php $podHas = ($podsestavaHasHistory[$ev->ID] ?? false); @endphp
                                                <x-mary-button
                                                    wire:click="openHistory('podsestava', {{ $ev->ID }}, '{{ trim($staDokl->doklad->MPSProjekt ?? '') }} {{ trim($staDokl->doklad->KlicDokla ?? '') }}', 'ŘÁDEK VP: {{ $radek->CisloRadk }} | POZICE: {{ trim($radek->Pozice ?? '-') }} | PODSESTAVA: {{ trim($ev->OznaceniPodsestavy ?? '') }}')"
                                                    icon="o-clock"
                                                    class="btn-sm btn-ghost {{ $podHas ? 'text-primary' : 'text-base-content/20' }}"
                                                    tooltip="{{ $podHas ? 'Práce na podsestavě' : 'Žádná historie prací' }}"
                                                    :disabled="!$podHas"
                                                />
                                                @can('manage zasobovani')
                                                    <x-mary-button
                                                        icon="o-pencil"
                                                        class="btn-sm btn-ghost text-primary"
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
                                        @endcan

                                    </div>
                                @endif
                            @endforeach

                            @can('manage zasobovani')
                                <x-mary-card title="Přidat nový záznam"
                                             class="p-2 bg-primary/5 border border-primary/20 rounded-lg">
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
                                            placeholder="0"
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
                            @endcan
                        </div>
                    </x-slot:content>
                </x-mary-collapse>
            </div>
        @empty
            <div class="text-center py-10 text-gray-500">
                <x-mary-icon name="o-inbox" class="w-12 h-12 mx-auto text-gray-300"/>
                <div class="mt-2">Žádné položky nenalezeny.</div>
            </div>
        @endforelse
    </x-mary-card>

    {{-- History Drawer --}}
    <x-mary-drawer wire:model="showHistoryDrawer" right title="Historie prací"
                   class="w-full lg:w-2/3" with-close-button>

        <div class="mb-4 px-3 py-3 bg-base-200/50 rounded-lg">
            @include('livewire.zasobovac.partials.vp-header', ['staDokl' => $staDokl, 'mistrUser' => $mistrUser, 'compact' => true, 'context' => $historyDrawerContext])
        </div>

        @if(count($historyDrawerRecords) > 0)
            @php $grouped = collect($historyDrawerRecords)->groupBy('operation'); @endphp
            <table class="table table-xs w-full">
                <thead>
                <tr class="text-xs text-gray-400 border-0">
                    <th class="!border-0">Operace</th>
                    <th class="!border-0">Zahájení</th>
                    <th class="!border-0">Operátor</th>
                    <th class="!border-0">Stroj</th>
                    <th class="!border-0">Čas</th>
                    <th class="!border-0">Mn.</th>
                    <th class="!border-0">Poznámka</th>
                </tr>
                </thead>
                <tbody>
                    <tr><td colspan="7" class="!p-0 !border-0"><hr class="border-base-300 my-1.5"></td></tr>
                @foreach($grouped as $operationName => $records)
                    @if(!$loop->first)
                        <tr><td colspan="7" class="!p-0 !border-0"><hr class="border-base-300 my-1.5"></td></tr>
                    @endif
                    @foreach($records as $rec)
                        <tr>
                            <td class="py-0.5 !border-0 font-bold">{{ $loop->first ? $operationName : '' }}</td>
                            <td class="py-0.5 !border-0 whitespace-nowrap tabular-nums">{{ $rec['started_at'] }}</td>
                            <td class="py-0.5 !border-0">{{ $rec['operator'] }}</td>
                            <td class="py-0.5 !border-0">{{ $rec['machine'] }}</td>
                            <td class="py-0.5 !border-0 whitespace-nowrap tabular-nums font-mono">{{ $rec['time'] }}</td>
                            <td class="py-0.5 !border-0">{{ $rec['quantity'] }} ks</td>
                            <td class="py-0.5 !border-0 max-w-32 truncate" title="{{ $rec['notes'] }}">{{ $rec['notes'] ?: '—' }}</td>
                        </tr>
                    @endforeach
                @endforeach
                </tbody>
            </table>
        @else
            <div class="text-center py-8 text-gray-500">Žádné záznamy.</div>
        @endif
    </x-mary-drawer>

    {{-- Print modal --}}
    @can('manage zasobovani')
        <x-mary-modal wire:model="showPrintModal" title="Tisk QR štítku" separator>
            <div class="space-y-4">
                <div class="text-sm text-gray-500">
                    @if($printType === 'doklad')
                        Tisk QR kódu celého výrobního příkazu <span
                            class="font-bold">{{ $staDokl->doklad->KlicDokla }}</span>
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
                <x-mary-button label="Zrušit" @click="$wire.showPrintModal = false"/>
                <x-mary-button
                    label="Tisknout"
                    icon="o-printer"
                    class="btn-primary"
                    wire:click="confirmPrint"
                    spinner="confirmPrint"
                />
            </x-slot:actions>
        </x-mary-modal>
    @endcan

</div>
