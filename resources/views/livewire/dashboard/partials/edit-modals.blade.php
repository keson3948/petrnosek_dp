{{-- ====== MODAL: Upravit VP ====== --}}
<x-mary-modal wire:model="showEditVpModal" title="Upravit výrobní příkaz" separator>
    <div class="flex flex-col">
        {{-- Vybraný VP --}}
        @if($edit_vp_sysPrimKlic && !$vpSearch)
            <div class="mb-4 p-3 border-2 border-primary bg-primary/10 rounded-lg flex items-center justify-between">
                <div>
                    <div class="text-xs text-gray-500">Vybraný VP</div>
                    <div class="font-bold font-mono text-lg text-primary">{{ $edit_vp_label }}</div>
                </div>
                <div class="flex items-center gap-2">
                    <x-mary-icon name="o-check-circle" class="w-6 h-6 text-primary" />
                    <button type="button" wire:click="clearVpSelection" class="btn btn-ghost btn-xs">
                        <x-mary-icon name="o-x-mark" class="w-4 h-4" />
                    </button>
                </div>
            </div>
        @endif

        <x-mary-input
            label="Hledat VP"
            wire:model.live.debounce.300ms="vpSearch"
            placeholder="Zadejte číslo VP nebo MPS..."
            icon="o-magnifying-glass"
            class="input-lg font-mono"
            clearable
        />

        @if($this->vpSearchResults->count() > 0)
            <div class="mt-4 space-y-2 overflow-y-auto max-h-72">
                @foreach($this->vpSearchResults as $doklad)
                    @php $isSelected = $edit_vp_sysPrimKlic === trim($doklad->SysPrimKlicDokladu); @endphp
                    <button type="button"
                        wire:click="selectVp('{{ addslashes(trim($doklad->SysPrimKlicDokladu)) }}', '{{ addslashes(trim($doklad->KlicDokla)) }}')"
                        class="w-full min-h-[3.5rem] p-3 text-left border-2 rounded-lg transition-colors flex items-center justify-between {{ $isSelected ? 'border-primary bg-primary/10' : 'border-base-200 hover:border-primary/30' }}">
                        <div>
                            <div class="font-bold font-mono text-lg {{ $isSelected ? 'text-primary' : '' }}">{{ trim($doklad->KlicDokla) }}</div>
                            @if(trim($doklad->MPSProjekt ?? ''))
                                <div class="text-sm text-gray-500">{{ trim($doklad->MPSProjekt) }}</div>
                            @endif
                        </div>
                        @if($isSelected)
                            <x-mary-icon name="o-check-circle" class="w-6 h-6 text-primary" />
                        @else
                            <x-mary-icon name="o-arrow-right" class="w-5 h-5 text-gray-400" />
                        @endif
                    </button>
                @endforeach
            </div>
        @elseif(strlen(trim($vpSearch)) >= 2 && $this->vpSearchResults->count() === 0)
            <div class="text-center border-2 border-dashed border-base-200 rounded-lg text-gray-500 py-8 mt-4">
                Žádný výrobní příkaz nenalezen.
            </div>
        @elseif(!$edit_vp_sysPrimKlic)
            <div class="text-center text-gray-400 py-6 text-sm mt-4">
                Zadejte alespoň 2 znaky pro vyhledávání
            </div>
        @endif
    </div>

    <x-slot:actions>
        <x-mary-button label="Zrušit" @click="$wire.showEditVpModal = false" />
        <x-mary-button label="Uložit" class="btn-primary" wire:click="saveEditVp" spinner="saveEditVp" />
    </x-slot:actions>
</x-mary-modal>

{{-- ====== MODAL: Upravit stroj a operaci ====== --}}
<x-mary-modal wire:model="showEditMachineOpModal" title="Upravit stroj a operaci" separator>
    <div class="flex flex-col">
        <div class="mb-6">
            <label class="label"><span class="label-text font-bold text-base text-gray-700 pb-2">Stroj</span></label>
            @if($this->userMachines->count() > 0)
                <div class="space-y-2">
                    @foreach($this->userMachines as $machine)
                        <button type="button"
                            wire:click="selectMachine('{{ $machine->machine_key }}')"
                            class="w-full min-h-[3.5rem] text-left border-2 rounded-lg p-3 transition-colors flex items-center {{ $edit_machine_id === $machine->machine_key ? 'border-secondary bg-secondary/10 text-secondary' : 'border-base-200 hover:border-secondary/30 text-gray-700' }}">
                            <x-mary-icon name="o-wrench-screwdriver" class="w-6 h-6 mr-3 {{ $edit_machine_id === $machine->machine_key ? 'text-secondary' : 'text-gray-400' }}" />
                            <span class="text-base font-semibold">{{ $machine->machine_name ?: $machine->machine_key }}</span>
                        </button>
                    @endforeach
                </div>
            @else
                <div class="text-gray-400 text-sm py-2">Nemáte přiřazeny žádné stroje. Kontaktujte správce.</div>
            @endif
        </div>

        <div>
            <label class="label"><span class="label-text font-bold text-base text-gray-700 pb-2">Operace</span></label>
            @if($edit_machine_id && $this->machineOperations->count() > 0)
                <div class="space-y-2">
                    @foreach($this->machineOperations as $op)
                        <button type="button"
                            wire:click="selectOperation('{{ $op->operation_key }}')"
                            class="w-full min-h-[3.5rem] text-left border-2 rounded-lg p-3 transition-colors flex items-center {{ $edit_operation_id === $op->operation_key ? 'border-primary bg-primary/10 text-primary' : 'border-base-200 hover:border-primary/30 text-gray-700' }}">
                            <x-mary-icon name="o-cog-6-tooth" class="w-6 h-6 mr-3 {{ $edit_operation_id === $op->operation_key ? 'text-primary' : 'text-gray-400' }}" />
                            <span class="text-base font-semibold">{{ $op->operation_name ?: $op->operation_key }}</span>
                        </button>
                    @endforeach
                </div>
            @elseif($edit_machine_id)
                <div class="text-gray-400 text-sm py-2">Pro tento stroj nejsou definovány operace.</div>
            @else
                <div class="text-gray-400 text-sm py-2">Nejprve vyberte stroj.</div>
            @endif
        </div>
    </div>

    <x-slot:actions>
        <x-mary-button label="Zrušit" @click="$wire.showEditMachineOpModal = false" />
        <x-mary-button label="Uložit" class="btn-primary" wire:click="saveEditMachineOp" spinner="saveEditMachineOp" />
    </x-slot:actions>
</x-mary-modal>

{{-- ====== MODAL: Upravit řádek a podsestavu ====== --}}
<x-mary-modal wire:model="showEditRadekPodsModal" title="Upravit řádek a podsestavu" separator>
    <div class="flex flex-col">
        @if(!$edit_sysPrimKlic)
            <div class="text-center text-gray-400 py-8">
                Záznam nemá přiřazený výrobní příkaz. Nejprve nastavte VP.
            </div>
        @else
            {{-- Řádek --}}
            <div class="mb-6">
                <label class="label"><span class="label-text font-bold text-base text-gray-700 pb-2">Řádek VP</span></label>

                @if($edit_radek_entita)
                    @php $selectedRadek = $this->editRadky->firstWhere('EntitaRad', $edit_radek_entita); @endphp
                    <div class="p-3 border-2 border-primary bg-primary/10 rounded-lg flex items-center justify-between mb-2">
                        <div>
                            <div class="font-bold text-primary">Poz. {{ trim($selectedRadek->Pozice ?? '—') }}</div>
                            @if($selectedRadek?->materialPolozka)
                                <div class="text-sm text-gray-500 truncate">{{ trim($selectedRadek->materialPolozka->Nazev1 ?? '') }}</div>
                            @endif
                        </div>
                        <button type="button" wire:click="editClearRadek" class="btn btn-ghost btn-xs">
                            <x-mary-icon name="o-x-mark" class="w-4 h-4" />
                        </button>
                    </div>
                @endif

                <div class="space-y-2 overflow-y-auto max-h-48">
                    @forelse($this->editRadky as $radek)
                        @php
                            $entita = $radek->EntitaRad;
                            $isSelected = $edit_radek_entita === $entita;
                            $podsCount = $radek->evPodsestavy->count();
                        @endphp
                        @if(!$isSelected)
                            <button type="button"
                                wire:click="editSelectRadek({{ $entita }})"
                                class="w-full text-left border-2 rounded-lg p-3 transition-colors border-base-200 hover:border-primary/30">
                                <div class="flex justify-between items-start mb-1">
                                    <div class="font-bold text-base">Poz. {{ trim($radek->Pozice) }}</div>
                                </div>
                                <div class="text-sm border-t border-base-200 pt-1 mt-1">
                                    @if($radek->materialPolozka)
                                        <div class="font-semibold text-gray-700 truncate">{{ trim($radek->materialPolozka->Nazev1 ?? '') }}</div>
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
                        @endif
                    @empty
                        <div class="text-center border-2 border-dashed border-base-200 rounded-lg text-gray-500 py-6">
                            VP neobsahuje žádné řádky.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Podsestava --}}
            @if($edit_radek_entita)
                <div>
                    <label class="label"><span class="label-text font-bold text-base text-gray-700 pb-2">Podsestava</span></label>

                    @if($edit_ev_podsestav_id)
                        @php $selectedPods = $this->editPodsestavy->firstWhere('ID', $edit_ev_podsestav_id); @endphp
                        <div class="p-3 border-2 border-primary bg-primary/10 rounded-lg flex items-center justify-between mb-2">
                            <div>
                                <div class="font-bold font-mono text-primary">{{ trim($selectedPods->OznaceniPodsestavy ?? '—') }}</div>
                                @if(trim($selectedPods->CisloVykresu ?? ''))
                                    <div class="text-sm text-gray-500">Výkres: {{ trim($selectedPods->CisloVykresu) }}</div>
                                @endif
                            </div>
                            <button type="button" wire:click="editClearPodsestava" class="btn btn-ghost btn-xs">
                                <x-mary-icon name="o-x-mark" class="w-4 h-4" />
                            </button>
                        </div>
                    @endif

                    @if($this->editPodsestavy->count() > 0)
                        <div class="space-y-2 overflow-y-auto max-h-48">
                            @foreach($this->editPodsestavy as $pods)
                                @php $isSelected = $edit_ev_podsestav_id === $pods->ID; @endphp
                                @if(!$isSelected)
                                    <button type="button"
                                        wire:click="editSelectPodsestava({{ $pods->ID }})"
                                        class="w-full text-left border-2 rounded-lg p-3 transition-colors border-base-200 hover:border-primary/30">
                                        <div class="font-bold font-mono text-base mb-1">{{ trim($pods->OznaceniPodsestavy ?? '—') }}</div>
                                        <div class="grid grid-cols-2 gap-x-2 gap-y-1 text-sm text-gray-600 border-t border-base-200 pt-1 mt-1">
                                            <div>Pozice: <span class="font-semibold text-gray-800">{{ trim($pods->Pozice ?? '—') }}</span></div>
                                            <div>Množství: <span class="font-semibold text-gray-800">{{ (int)($pods->Mnozstvi ?? 0) }} ks</span></div>
                                            @if(trim($pods->CisloVykresu ?? ''))
                                                <div class="col-span-2">Výkres: <span class="font-semibold text-gray-800">{{ trim($pods->CisloVykresu) }}</span></div>
                                            @endif
                                        </div>
                                    </button>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-gray-400 text-sm py-4">
                            Tento řádek nemá žádné podsestavy.
                        </div>
                    @endif
                </div>
            @endif
        @endif
    </div>

    <x-slot:actions>
        <x-mary-button label="Zrušit" @click="$wire.showEditRadekPodsModal = false" />
        <x-mary-button label="Uložit" class="btn-primary" wire:click="saveEditRadekPodsestava" spinner="saveEditRadekPodsestava" />
    </x-slot:actions>
</x-mary-modal>

{{-- ====== MODAL: Upravit výkres ====== --}}
<x-mary-modal wire:model="showEditDrawingModal" title="Upravit číslo výkresu" separator>
    <x-mary-input label="Číslo výkresu" wire:model="edit_drawing_only" placeholder="Zadejte číslo výkresu" class="input-lg" />
    <x-slot:actions>
        <x-mary-button label="Zrušit" @click="$wire.showEditDrawingModal = false" />
        <x-mary-button label="Uložit" class="btn-primary" wire:click="saveEditDrawing" spinner="saveEditDrawing" />
    </x-slot:actions>
</x-mary-modal>

{{-- ====== MODAL: Upravit poznámku ====== --}}
<x-mary-modal wire:model="showEditNotesModal" title="Upravit poznámku" separator>
    <x-mary-textarea label="Poznámka" wire:model="edit_notes" placeholder="Zadejte poznámku..." rows="4" />
    <x-slot:actions>
        <x-mary-button label="Zrušit" @click="$wire.showEditNotesModal = false" />
        <x-mary-button label="Uložit" class="btn-primary" wire:click="saveEditNotes" spinner="saveEditNotes" />
    </x-slot:actions>
</x-mary-modal>

{{-- ====== MODAL: Upravit množství ====== --}}
<x-mary-modal wire:model="showEditQuantityModal" title="Upravit množství" separator>
    <div x-data="{ qty: @entangle('edit_quantity_init') }" class="flex flex-col items-center py-4">
        <div class="flex items-center gap-4">
            <button type="button" @click="qty = Math.max(0, qty - 10)" class="btn btn-lg">-10</button>
            <button type="button" @click="qty = Math.max(0, qty - 1)" class="btn btn-lg">
                <x-mary-icon name="o-minus" class="w-5 h-5" />
            </button>
            <div class="text-5xl font-bold w-28 text-center tabular-nums" x-text="qty"></div>
            <button type="button" @click="qty++" class="btn btn-lg">
                <x-mary-icon name="o-plus" class="w-5 h-5" />
            </button>
            <button type="button" @click="qty += 10" class="btn btn-lg">+10</button>
        </div>
        <div class="text-sm text-gray-500 mt-3">kusů</div>

        <div class="w-full flex justify-end gap-2 mt-6">
            <x-mary-button label="Zrušit" @click="$wire.showEditQuantityModal = false" />
            <x-mary-button label="Uložit" class="btn-primary" @click="$wire.saveEditQuantity(qty)" />
        </div>
    </div>
</x-mary-modal>

{{-- ====== MODAL: Upravit čas ====== --}}
<x-mary-modal wire:model="showEditTimeModal" title="Upravit odpracovaný čas" separator>
    <div x-data="{
        h: 0,
        m: 0,
        startedAt: '',
        init() {
            this.$watch('$wire.edit_time_init', (val) => {
                if (val) {
                    this.h = val.hours ?? 0;
                    this.m = val.minutes ?? 0;
                    this.startedAt = val.started_at ?? '';
                }
            });
            let val = $wire.edit_time_init;
            if (val) {
                this.h = val.hours ?? 0;
                this.m = val.minutes ?? 0;
                this.startedAt = val.started_at ?? '';
            }
        },
        addH(d) { this.h = Math.max(0, this.h + d) },
        addM(d) {
            let n = this.m + d;
            if (n >= 60) { this.h++; n -= 60; }
            else if (n < 0) { if (this.h > 0) { this.h--; n += 60; } else { n = 0; } }
            this.m = n;
        },
        pad(v) { return String(v).padStart(2, '0'); }
    }">
        <div class="mb-4">
            <x-mary-input label="Začátek" x-model="startedAt" type="datetime-local" name=""></x-mary-input>
        </div>

        <label class="label"><span class="label-text font-semibold">Odpracovaná doba</span></label>
        <div class="flex items-center justify-center gap-6 py-4">
            <div class="flex flex-col items-center">
                <span class="text-xs text-gray-400 uppercase mb-2">Hodiny</span>
                <button type="button" @click="addH(1)" class="btn">
                    <x-mary-icon name="o-plus" class="w-5 h-5" />
                </button>
                <div class="text-5xl font-bold my-3 w-20 text-center tabular-nums" x-text="pad(h)"></div>
                <button type="button" @click="addH(-1)" class="btn">
                    <x-mary-icon name="o-minus" class="w-5 h-5" />
                </button>
            </div>

            <div class="text-5xl font-bold text-gray-300 mt-6">:</div>

            <div class="flex flex-col items-center">
                <span class="text-xs text-gray-400 uppercase mb-2">Minuty</span>
                <div class="flex gap-2">
                    <button type="button" @click="addM(10)" class="btn">+10</button>
                    <button type="button" @click="addM(1)" class="btn">+1</button>
                </div>
                <div class="text-5xl font-bold my-3 w-20 text-center tabular-nums" x-text="pad(m)"></div>
                <div class="flex gap-2">
                    <button type="button" @click="addM(-10)" class="btn">-10</button>
                    <button type="button" @click="addM(-1)" class="btn">-1</button>
                </div>
            </div>
        </div>

        <div class="text-center text-sm text-gray-500 mt-2">
            Celkem: <span x-text="h"></span> hodin <span x-text="m"></span> minut
        </div>

        @error('edit_time')
            <div class="text-error text-sm mt-2">{{ $message }}</div>
        @enderror

        <div class="flex justify-end gap-2 mt-6">
            <x-mary-button label="Zrušit" @click="$wire.showEditTimeModal = false" />
            <button type="button" class="btn btn-primary" @click="$wire.saveEditTime(h, m, startedAt)">Uložit</button>
        </div>
    </div>
</x-mary-modal>
