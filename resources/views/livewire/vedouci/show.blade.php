<div>
    <x-mary-header :title="$operator->name" separator>
        <x-slot:subtitle>
            Klíč: {{ $operator->klic_subjektu ?? '—' }}
        </x-slot:subtitle>
        <x-slot:actions>
            <x-mary-button label="Zpět" icon="o-arrow-left" link="{{ route('vedouci.index') }}" />
            <x-mary-button label="Přidat záznam" icon="o-plus" class="btn-primary" wire:click="openAddModal" />
        </x-slot:actions>
    </x-mary-header>

    {{-- Aktivní operace --}}
    @if($this->activeRecord)
        @php $active = $this->activeRecord; @endphp
        <x-mary-card title="Aktuálně pracuje" class="mb-6 border-2 {{ $active->status == 1 ? 'border-warning' : 'border-success' }}">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <div class="text-xs text-gray-400 uppercase">Stav</div>
                    <x-mary-badge :value="$active->status == 1 ? 'Pauza' : 'Pracuje'"
                        class="{{ $active->status == 1 ? 'badge-warning' : 'badge-success' }}" />
                </div>
                <div>
                    <div class="text-xs text-gray-400 uppercase">Stroj</div>
                    <div class="font-semibold">{{ trim($active->machine?->NazevUplny ?? $active->machine_id ?? '') ?: '—' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 uppercase">VP</div>
                    <div class="font-mono font-semibold">{{ trim($active->doklad?->KlicDokla ?? '') ?: '—' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 uppercase">Operace</div>
                    <div class="font-semibold">{{ trim($active->operation?->Nazev1 ?? $active->operation_id ?? '') ?: '—' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 uppercase">Zahájeno</div>
                    <div class="tabular-nums">{{ $active->started_at?->format('d.m.Y H:i') ?? '—' }}</div>
                </div>
            </div>
        </x-mary-card>
    @endif

    {{-- Filters --}}
    <div class="flex flex-wrap items-end gap-4 mb-6">
        <x-mary-input label="Od" type="date" wire:model.live="dateFrom" class="w-40" />
        <x-mary-input label="Do" type="date" wire:model.live="dateTo" class="w-40" />
    </div>

    {{-- Records Table --}}
    <x-mary-card>
        <x-mary-table :headers="$headers" :rows="$this->records" striped>

            @scope('cell_started_at', $record)
                <span class="whitespace-nowrap tabular-nums">{{ $record->started_at?->format('d.m.Y H:i') ?? '—' }}</span>
            @endscope

            @scope('cell_ended_at', $record)
                <span class="whitespace-nowrap tabular-nums">{{ $record->ended_at?->format('d.m.Y H:i') ?? '—' }}</span>
            @endscope

            @scope('cell_vp', $record)
                <span class="font-mono">{{ trim($record->doklad?->KlicDokla ?? '') ?: '—' }}</span>
            @endscope

            @scope('cell_machine', $record)
                {{ trim($record->machine?->NazevUplny ?? $record->machine_id ?? '') ?: '—' }}
            @endscope

            @scope('cell_operation', $record)
                {{ trim($record->operation?->Nazev1 ?? $record->operation_id) }}
            @endscope

            @scope('cell_quantity', $record)
                {{ $record->processed_quantity ?? 0 }} ks
            @endscope

            @scope('cell_time', $record)
                @php
                    $workedH = null;
                    $workedM = null;
                    if ($record->started_at && $record->ended_at) {
                        $totalMinutes = max(0, intval($record->started_at->diffInMinutes($record->ended_at)) - ($record->total_paused_min ?? 0));
                        $workedH = intdiv($totalMinutes, 60);
                        $workedM = $totalMinutes % 60;
                    }
                @endphp
                <span class="whitespace-nowrap tabular-nums">
                    @if($workedH !== null)
                        {{ $workedH }}:{{ str_pad($workedM, 2, '0', STR_PAD_LEFT) }}
                    @else
                        —
                    @endif
                </span>
            @endscope

            @scope('cell_notes', $record)
                <span class="max-w-48 truncate block">{{ $record->notes ?: '—' }}</span>
            @endscope

            @scope('cell_actions', $record)
                <div class="text-right whitespace-nowrap">
                    <x-mary-button icon="o-pencil" wire:click="openEditModal({{ $record->ID }})" class="btn-ghost btn-sm" spinner />
                    <x-mary-button icon="o-trash" wire:click="deleteRecord({{ $record->ID }})" wire:confirm="Opravdu smazat tento záznam?" class="btn-ghost btn-sm text-error" spinner />
                </div>
            @endscope

        </x-mary-table>

        <div class="mt-4 text-sm text-gray-500">
            Celkem {{ $this->records->count() }} záznamů
            @php
                $totalMin = $this->records->sum(function ($r) {
                    if ($r->started_at && $r->ended_at) {
                        return max(0, intval($r->started_at->diffInMinutes($r->ended_at)) - ($r->total_paused_min ?? 0));
                    }
                    return 0;
                });
            @endphp
            | Celkem odpracováno: {{ intdiv($totalMin, 60) }}h {{ $totalMin % 60 }}min
        </div>
    </x-mary-card>

    {{-- ====== MODAL: Přidat / Upravit záznam (krokový) ====== --}}
    <x-mary-drawer wire:model="showModal"
        right
        :title="match($modalStep) {
            1 => 'Vyberte výrobní příkaz',
            2 => 'Vyberte řádek VP',
            3 => 'Vyberte podsestavu',
            4 => 'Číslo výkresu',
            5 => ($modalMode === 'edit' ? 'Upravit záznam' : 'Stroj, operace a čas'),
            default => ($modalMode === 'edit' ? 'Upravit záznam' : 'Nový záznam'),
        }"
        :subtitle="'Krok ' . $modalStep . '/5'"
        separator
        box-class="max-w-2xl"
        persistent
        class="w-full lg:w-1/3"
    >
        {{-- Step progress indicator --}}
        <div class="flex items-center justify-center gap-1.5 pb-4">
            @foreach([1, 2, 3, 4, 5] as $i)
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-colors
                    {{ $modalStep === $i ? 'bg-primary text-white ring-2 ring-primary/30' : ($modalStep > $i ? 'bg-success/20 text-success' : 'bg-base-200 text-base-content/30') }}">
                    @if($modalStep > $i)
                        <x-mary-icon name="o-check" class="w-4 h-4" />
                    @else
                        {{ $i }}
                    @endif
                </div>
                @if($i < 5)
                    <div class="w-4 h-0.5 {{ $modalStep > $i ? 'bg-success' : 'bg-base-200' }}"></div>
                @endif
            @endforeach
        </div>

        {{-- Summary breadcrumb --}}
        @if($modalStep >= 2 && $m_sysPrimKlic)
            @php $summaryDoklad = $this->mSelectedDoklad; @endphp
            <div class="bg-base-200 rounded-lg px-4 py-3 mb-4 space-y-1">
                <div class="text-lg font-bold leading-tight truncate">
                    {{ trim($summaryDoklad->MPSProjekt ?? '') ?: '—' }}
                    <span class="text-base font-mono text-gray-500 ml-1">{{ trim($summaryDoklad->KlicDokla ?? '') ?: '—' }}</span>
                </div>
                <div class="flex flex-wrap gap-x-5 gap-y-1 text-sm text-gray-500">
                    @if($m_radekEntita && $modalStep >= 3)
                        @php $summaryRadek = $this->mDokladRadky->firstWhere('EntitaRad', $m_radekEntita); @endphp
                        <span>Poz. <strong class="text-gray-800">{{ trim($summaryRadek->Pozice ?? '-') }}</strong></span>
                    @endif
                    @if($m_evPodsestavId && $modalStep >= 4)
                        @php $summaryPods = $this->mEvPodsestav; @endphp
                        <span>Pods. <strong class="text-gray-800">{{ trim($summaryPods->OznaceniPodsestavy ?? $m_evPodsestavId) }}</strong></span>
                    @endif
                    @if($m_drawing_number && $modalStep >= 5)
                        <span>Výkres <strong class="text-gray-800">{{ $m_drawing_number }}</strong></span>
                    @endif
                </div>
            </div>
        @endif

        {{-- Step content --}}
        <div class="min-h-[20rem] max-h-[50vh] overflow-y-auto">

            {{-- Step 1: VP search --}}
            @if($modalStep === 1)
                <div class="flex flex-col h-full">
                    @if($m_sysPrimKlic && !$m_vpSearch)
                        <div class="mb-4 p-3 border-2 border-primary bg-primary/10 rounded-lg flex items-center justify-between shrink-0">
                            <div>
                                <div class="text-xs text-gray-500">Vybraný VP</div>
                                <div class="font-bold font-mono text-lg text-primary">{{ trim($this->mSelectedDoklad?->KlicDokla ?? '') }}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-mary-icon name="o-check-circle" class="w-6 h-6 text-primary" />
                                <button type="button" wire:click="mClearDoklad" class="btn btn-ghost btn-xs">
                                    <x-mary-icon name="o-x-mark" class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    @endif

                    <x-mary-input
                        label="Hledat výrobní příkaz (VP) nebo projekt"
                        wire:model.live.debounce.300ms="m_vpSearch"
                        placeholder="Zadejte číslo VP nebo MPS..."
                        icon="o-magnifying-glass"
                        class="input-lg font-mono"
                        clearable
                    />

                    @if($this->mVpSearchResults->count() > 0)
                        <div class="mt-4 space-y-2">
                            @foreach($this->mVpSearchResults as $doklad)
                                @php $isSelected = $m_sysPrimKlic === trim($doklad->SysPrimKlicDokladu); @endphp
                                <button type="button"
                                    wire:click="mSelectDoklad('{{ addslashes(trim($doklad->SysPrimKlicDokladu)) }}')"
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
                    @elseif(strlen(trim($m_vpSearch)) >= 2 && $this->mVpSearchResults->count() === 0)
                        <div class="text-center border-2 border-dashed border-base-200 rounded-lg text-gray-500 py-8 mt-4">
                            Žádný výrobní příkaz nenalezen.
                        </div>
                    @elseif(!$m_sysPrimKlic)
                        <div class="text-center text-gray-400 py-10 text-sm">
                            Zadejte alespoň 2 znaky pro vyhledávání
                        </div>
                    @endif
                </div>

            {{-- Step 2: Řádek VP --}}
            @elseif($modalStep === 2)
                <div class="space-y-2">
                    @php $radky = $this->mDokladRadky; @endphp

                    @forelse($radky as $radek)
                        @php
                            $entita = $radek->EntitaRad;
                            $isSelected = $m_radekEntita === $entita;
                            $podsCount = $radek->evPodsestavy->count();
                        @endphp
                        <button type="button"
                            wire:click="mSelectRadek({{ $entita }})"
                            class="w-full text-left border-2 rounded-lg p-3 transition-colors {{ $isSelected ? 'border-primary bg-primary/10' : 'border-base-200 hover:border-primary/30' }}">
                            <div class="flex justify-between items-start mb-1">
                                <div class="font-bold text-base {{ $isSelected ? 'text-primary' : '' }}">
                                    Poz. {{ trim($radek->Pozice) }}
                                    @if(trim($radek->Pozice ?? '') !== '')
                                        | {{ $radek->Polozka ?? '—' }}
                                    @endif
                                </div>
                            </div>
                            <div class="text-sm border-t border-base-200 pt-1 mt-1">
                                @if($radek->materialPolozka)
                                    <div class="font-semibold text-gray-700 truncate">{{ trim($radek->materialPolozka->Nazev1 ?? '') }}</div>
                                @endif
                                <div class="text-gray-500 flex items-center justify-between mt-1">
                                    <span>Množ.: {{ (float)($radek->MnozstviZMJ ?? 0) }} ks</span>
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

            {{-- Step 3: Podsestava --}}
            @elseif($modalStep === 3)
                <div class="space-y-2">
                    @php $podsestavy = $this->mRadekPodsestavy; @endphp

                    @forelse($podsestavy as $pods)
                        @php $isSelected = $m_evPodsestavId === $pods->ID; @endphp
                        <button type="button"
                            wire:click="mSelectPodsestava({{ $pods->ID }})"
                            class="w-full text-left border-2 rounded-lg p-3 transition-colors {{ $isSelected ? 'border-primary bg-primary/10' : 'border-base-200 hover:border-primary/30' }}">
                            <div class="font-bold font-mono text-base mb-1 {{ $isSelected ? 'text-primary' : '' }}">
                                {{ trim($pods->OznaceniPodsestavy ?? '—') }}
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

            {{-- Step 4: Číslo výkresu --}}
            @elseif($modalStep === 4)
                <div class="py-4">
                    <x-mary-input
                        label="Číslo referenčního výkresu"
                        wire:model.live.debounce.100ms="m_drawing_number"
                        placeholder="Např. VYK-2026-001"
                        class="input-lg font-mono"
                        hint="Volitelné – můžete pokračovat bez vyplnění."
                        clearable
                    />
                </div>

            {{-- Step 5: Stroj, operace, čas --}}
            @elseif($modalStep === 5)
                <div class="flex flex-col gap-5">
                    {{-- Stroj --}}
                    <div>
                        <label class="label"><span class="label-text font-bold text-base text-gray-700 pb-2">Stroj</span></label>
                        @if($this->operatorMachines->count() > 0)
                            <div class="space-y-2">
                                @foreach($this->operatorMachines as $machine)
                                    <button type="button"
                                        wire:click="mSelectMachine('{{ $machine->machine_key }}')"
                                        class="w-full min-h-[3rem] text-left border-2 rounded-lg p-3 transition-colors flex items-center {{ $m_machine_id === $machine->machine_key ? 'border-secondary bg-secondary/10 text-secondary' : 'border-base-200 hover:border-secondary/30 text-gray-700' }}">
                                        <x-mary-icon name="o-wrench-screwdriver" class="w-5 h-5 mr-3 {{ $m_machine_id === $machine->machine_key ? 'text-secondary' : 'text-gray-400' }}" />
                                        <span class="text-base font-semibold">{{ $machine->machine_name ?: $machine->machine_key }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @else
                            <x-mary-input wire:model.live.debounce.300ms="m_machine_id" placeholder="Zadejte stroj" />
                        @endif
                    </div>

                    {{-- Operace --}}
                    <div>
                        <label class="label"><span class="label-text font-bold text-base text-gray-700 pb-2">Operace</span></label>
                        @if($m_machine_id && $this->mMachineOperations->count() > 0)
                            <div class="space-y-2">
                                @foreach($this->mMachineOperations as $op)
                                    <button type="button"
                                        wire:click="mSelectOperation('{{ $op->operation_key }}')"
                                        class="w-full min-h-[3rem] text-left border-2 rounded-lg p-3 transition-colors flex items-center {{ $m_operation_id === $op->operation_key ? 'border-primary bg-primary/10 text-primary' : 'border-base-200 hover:border-primary/30 text-gray-700' }}">
                                        <x-mary-icon name="o-cog-6-tooth" class="w-5 h-5 mr-3 {{ $m_operation_id === $op->operation_key ? 'text-primary' : 'text-gray-400' }}" />
                                        <span class="text-base font-semibold">{{ $op->operation_name ?: $op->operation_key }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @elseif(!$m_machine_id)
                            <div class="text-sm text-gray-400">Vyberte stroj.</div>
                        @else
                            <x-mary-input wire:model="m_operation_id" placeholder="Zadejte operaci" />
                        @endif
                        @error('m_operation_id') <span class="text-error text-sm">{{ $message }}</span> @enderror
                    </div>

                    {{-- Čas --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-mary-input label="Začátek" type="datetime-local" wire:model="m_startedAt" />
                            @error('m_startedAt') <span class="text-error text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <x-mary-input label="Konec" type="datetime-local" wire:model="m_endedAt" />
                            @error('m_endedAt') <span class="text-error text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Množství --}}
                    <x-mary-input label="Množství (ks)" type="number" wire:model="m_quantity" min="0" />
                    @error('m_quantity') <span class="text-error text-sm">{{ $message }}</span> @enderror

                    {{-- Poznámka --}}
                    <x-mary-textarea label="Poznámka" wire:model="m_notes" rows="2" />
                </div>
            @endif

        </div>

        {{-- Footer --}}
        <x-slot:actions>
            <div class="flex items-center justify-between w-full">
                @if($modalStep > 1)
                    <x-mary-button label="Zpět" icon="o-arrow-left" wire:click="mPrevStep" />
                @else
                    <x-mary-button label="Zrušit" @click="$wire.showModal = false" />
                @endif

                @if($modalStep === 5)
                    <x-mary-button
                        :label="$modalMode === 'edit' ? 'Uložit' : 'Přidat'"
                        icon="o-check"
                        class="btn-primary"
                        wire:click="saveRecord"
                        spinner="saveRecord"
                    />
                @elseif($modalStep === 2)
                    <x-mary-button label="Pokračovat bez řádku" icon-right="o-arrow-right" wire:click="mSkipRadek" class="btn-outline" />
                @elseif($modalStep === 3)
                    <x-mary-button label="Pokračovat bez podsestavy" icon-right="o-arrow-right" wire:click="mSkipPodsestava" class="btn-outline" />
                @elseif($modalStep === 4)
                    @if($m_drawing_number)
                        <x-mary-button label="Dále" icon-right="o-arrow-right" wire:click="mNextStep" class="btn-primary" />
                    @else
                        <x-mary-button label="Pokračovat bez výkresu" icon-right="o-arrow-right" wire:click="mSkipDrawingNumber" class="btn-outline" />
                    @endif
                @else
                    <x-mary-button label="Dále" icon-right="o-arrow-right" wire:click="mNextStep" class="btn-primary" />
                @endif
            </div>
        </x-slot:actions>
    </x-mary-modal>
</div>
