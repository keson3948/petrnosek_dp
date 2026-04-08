<div>
    <x-mary-header :title="$editRecordId ? 'Upravit záznam' : 'Nový záznam'" separator>
        <x-slot:subtitle>Operátor: {{ $operator->name }} ({{ $operator->klic_subjektu }})</x-slot:subtitle>
        <x-slot:actions>
            <x-mary-button label="Zpět" icon="o-arrow-left" link="{{ route('vedouci.show', $operator->klic_subjektu) }}" />
        </x-slot:actions>
    </x-mary-header>

    <div class="sticky top-0 z-10 bg-base-100/95 backdrop-blur border-b border-base-200 rounded-lg px-4 py-3 mb-6">
        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm">
            <span class="font-bold text-base-content">{{ $operator->name }}</span>
            <span class="text-gray-300">|</span>

            @if($sysPrimKlic)
                @php $doklad = $this->selectedDoklad; @endphp
                <span class="font-mono font-semibold text-primary">{{ trim($doklad->MPSProjekt ?? '') }} {{ trim($doklad->KlicDokla ?? '') }}</span>
            @else
                <span class="text-gray-400">VP nevybráno</span>
            @endif

            @if($radekEntita)
                @php $sr = $this->dokladRadky->firstWhere('EntitaRad', $radekEntita); @endphp
                <span class="text-gray-300">&rsaquo;</span>
                <span>Poz. <strong>{{ trim($sr->Pozice ?? '-') }}</strong></span>
            @endif

            @if($evPodsestavId)
                @php $sp = $this->selectedEvPodsestav; @endphp
                <span class="text-gray-300">&rsaquo;</span>
                <span>Pods. <strong>{{ trim($sp->OznaceniPodsestavy ?? '-') }}</strong></span>
            @endif

            @if($drawingNumber)
                <span class="text-gray-300">&rsaquo;</span>
                <span>Výkres <strong>{{ $drawingNumber }}</strong></span>
            @endif

            <span class="text-gray-300">|</span>

            @if($machineId)
                @php $sm = $this->selectedMachine; @endphp
                <span class="text-secondary font-semibold">{{ $sm?->machine_name ?: $machineId }}</span>
            @else
                <span class="text-gray-400">Stroj nevybrán</span>
            @endif

            @if($operationId)
                @php $so = $this->selectedOperation; @endphp
                <span class="text-gray-300">&rsaquo;</span>
                <span>{{ $so?->operation_name ?: $operationId }}</span>
            @endif
        </div>
    </div>

    <div class="space-y-6">

        <x-mary-card title="1. Výrobní příkaz" shadow separator>
            @if($sysPrimKlic)
                @php $doklad = $this->selectedDoklad; @endphp
                <div class="flex items-center justify-between p-3 border-2 border-primary bg-primary/5 rounded-lg">
                    <div>
                        <div class="text-lg font-bold font-mono text-primary">
                            {{ trim($doklad->MPSProjekt ?? '') }} {{ trim($doklad->KlicDokla ?? '') }}
                        </div>
                        @if(trim($doklad->SpecifiSy ?? ''))
                            <div class="text-sm text-gray-500">{{ trim($doklad->SpecifiSy) }}</div>
                        @endif
                    </div>
                    <x-mary-button icon="o-x-mark" wire:click="clearDoklad" class="btn-ghost btn-sm" tooltip="Zrušit výběr" />
                </div>
            @else
                <x-mary-input
                    wire:model.live.debounce.300ms="vpSearch"
                    placeholder="Hledat VP, projekt, zakázku..."
                    icon="o-magnifying-glass"
                    class="input-lg font-mono"
                    clearable
                />
                @error('sysPrimKlic') <div class="text-error text-sm mt-2">{{ $message }}</div> @enderror

                @if($this->vpSearchResults->count() > 0)
                    <div class="mt-3 space-y-1.5 max-h-72 overflow-y-auto">
                        @foreach($this->vpSearchResults as $d)
                            <button type="button"
                                wire:click="selectDoklad('{{ addslashes(trim($d->SysPrimKlicDokladu)) }}')"
                                class="w-full text-left border rounded-lg p-3 hover:border-primary/50 hover:bg-primary/5 transition-colors">
                                <span class="font-bold font-mono">{{ trim($d->KlicDokla) }}</span>
                                @if(trim($d->MPSProjekt ?? ''))
                                    <span class="text-gray-500 ml-2">{{ trim($d->MPSProjekt) }}</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                @elseif(strlen(trim($vpSearch)) >= 2)
                    <div class="text-center text-gray-400 py-6 mt-3">Nic nenalezeno.</div>
                @else
                    <div class="text-center text-gray-400 py-4 text-sm mt-2">Zadejte alespoň 2 znaky</div>
                @endif
            @endif
        </x-mary-card>

        @if($sysPrimKlic)
            <x-mary-card title="2. Řádek VP" subtitle="Volitelné" shadow separator>
                @if($radekEntita)
                    @php $selRadek = $this->dokladRadky->firstWhere('EntitaRad', $radekEntita); @endphp
                    <div class="flex items-center justify-between p-3 border-2 border-primary bg-primary/5 rounded-lg">
                        <div>
                            <span class="font-bold">Poz. {{ trim($selRadek->Pozice ?? '-') }}</span>
                            @if($selRadek?->materialPolozka)
                                <span class="text-gray-600 ml-2">{{ trim($selRadek->materialPolozka->Nazev1 ?? '') }}</span>
                            @endif
                            <span class="text-sm text-gray-400 ml-2">{{ (float)($selRadek->MnozstviZMJ ?? 0) }} ks</span>
                        </div>
                        <x-mary-button icon="o-x-mark" wire:click="clearRadek" class="btn-ghost btn-sm" tooltip="Zrušit výběr" />
                    </div>
                @else
                    @if($this->dokladRadky->count() > 5)
                        <x-mary-input wire:model.live.debounce.200ms="radekFilter" placeholder="Filtrovat řádky..." icon="o-magnifying-glass" class="mb-3" clearable />
                    @endif
                    @error('radekEntita') <div class="text-error text-sm mb-2">{{ $message }}</div> @enderror

                    <div class="space-y-1.5 max-h-72 overflow-y-auto">
                        @forelse($this->dokladRadky as $radek)
                            <button type="button"
                                wire:click="selectRadek({{ $radek->EntitaRad }})"
                                class="w-full text-left border rounded-lg p-3 hover:border-primary/50 hover:bg-primary/5 transition-colors">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <span class="font-bold">Poz. {{ trim($radek->Pozice) }}</span>
                                        @if($radek->materialPolozka)
                                            <span class="text-gray-600 ml-2 text-sm">{{ trim($radek->materialPolozka->Nazev1 ?? '') }}</span>
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-400 whitespace-nowrap ml-2">
                                        {{ (float)($radek->MnozstviZMJ ?? 0) }} ks
                                        @if($radek->evPodsestavy->count() > 0)
                                            <span class="badge badge-info badge-sm ml-1">{{ $radek->evPodsestavy->count() }} pods.</span>
                                        @endif
                                    </div>
                                </div>
                            </button>
                        @empty
                            <div class="text-center text-gray-400 py-6">Žádné řádky.</div>
                        @endforelse
                    </div>
                @endif
            </x-mary-card>
        @endif

        @if($radekEntita && $this->radekPodsestavy->count() > 0)
            <x-mary-card title="3. Podsestava" subtitle="Volitelné" shadow separator>
                @if($evPodsestavId)
                    @php $selPods = $this->selectedEvPodsestav; @endphp
                    <div class="flex items-center justify-between p-3 border-2 border-primary bg-primary/5 rounded-lg">
                        <div>
                            <span class="font-bold font-mono">{{ trim($selPods->OznaceniPodsestavy ?? '-') }}</span>
                            <span class="text-gray-600 ml-2">poz. {{ trim($selPods->Pozice ?? '-') }}</span>
                            <span class="text-sm text-gray-400 ml-2">{{ (int)($selPods->Mnozstvi ?? 0) }} ks</span>
                            @if(trim($selPods->CisloVykresu ?? ''))
                                <span class="text-sm text-gray-500 ml-2">| výkres: {{ trim($selPods->CisloVykresu) }}</span>
                            @endif
                        </div>
                        <x-mary-button icon="o-x-mark" wire:click="clearPodsestava" class="btn-ghost btn-sm" tooltip="Zrušit výběr" />
                    </div>
                @else
                    @if($this->radekPodsestavy->count() > 5)
                        <x-mary-input wire:model.live.debounce.200ms="podsFilter" placeholder="Filtrovat podsestavy..." icon="o-magnifying-glass" class="mb-3" clearable />
                    @endif
                    @error('evPodsestavId') <div class="text-error text-sm mb-2">{{ $message }}</div> @enderror

                    <div class="space-y-1.5 max-h-60 overflow-y-auto">
                        @foreach($this->radekPodsestavy as $pods)
                            <button type="button"
                                wire:click="selectPodsestava({{ $pods->ID }})"
                                class="w-full text-left border rounded-lg p-3 hover:border-primary/50 hover:bg-primary/5 transition-colors">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <span class="font-bold font-mono">{{ trim($pods->OznaceniPodsestavy ?? '-') }}</span>
                                        <span class="text-gray-600 ml-2">poz. {{ trim($pods->Pozice ?? '-') }}</span>
                                    </div>
                                    <div class="text-sm text-gray-400 whitespace-nowrap ml-2">
                                        {{ (int)($pods->Mnozstvi ?? 0) }} ks
                                        @if(trim($pods->CisloVykresu ?? ''))
                                            <span class="ml-1">| {{ trim($pods->CisloVykresu) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </button>
                        @endforeach
                    </div>
                @endif
            </x-mary-card>
        @endif

        @if($sysPrimKlic)
            <x-mary-card title="4. Číslo výkresu" subtitle="Volitelné" shadow separator>
                <x-mary-input
                    wire:model.live.debounce.100ms="drawingNumber"
                    placeholder="Číslo referenčního výkresu"
                    class="font-mono"
                    clearable
                />
            </x-mary-card>
        @endif

        <x-mary-card title="5. Stroj" shadow separator>
            @error('machineId') <div class="text-error text-sm mb-2">{{ $message }}</div> @enderror

            @if($this->availableMachines->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach($this->availableMachines as $machine)
                        <button type="button"
                            wire:click="selectMachine('{{ $machine->machine_key }}')"
                            class="text-left border-2 rounded-lg p-3 transition-colors flex items-center gap-3
                                {{ $machineId === $machine->machine_key
                                    ? 'border-secondary bg-secondary/10 ring-2 ring-secondary/20'
                                    : 'border-base-200 hover:border-secondary/30 hover:bg-secondary/5' }}">
                            <x-mary-icon name="o-wrench-screwdriver"
                                class="w-5 h-5 shrink-0 {{ $machineId === $machine->machine_key ? 'text-secondary' : 'text-gray-400' }}" />
                            <div class="min-w-0">
                                <div class="font-semibold truncate {{ $machineId === $machine->machine_key ? 'text-secondary' : '' }}">
                                    {{ $machine->machine_name ?: $machine->machine_key }}
                                </div>
                                @if(! $machine->assigned)
                                    <div class="text-xs text-gray-400">volný stroj</div>
                                @endif
                            </div>
                            @if($machineId === $machine->machine_key)
                                <x-mary-icon name="o-check-circle" class="w-5 h-5 text-secondary shrink-0 ml-auto" />
                            @endif
                        </button>
                    @endforeach
                </div>
            @else
                <x-mary-input wire:model.live.debounce.300ms="machineId" placeholder="Zadejte klíč stroje" />
            @endif
        </x-mary-card>

        <x-mary-card title="6. Operace" shadow separator>
            @error('operationId') <div class="text-error text-sm mb-2">{{ $message }}</div> @enderror

            @if($machineId && $this->machineOperations->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach($this->machineOperations as $op)
                        <button type="button"
                            wire:click="selectOperation('{{ $op->operation_key }}')"
                            class="text-left border-2 rounded-lg p-3 transition-colors flex items-center gap-3
                                {{ $operationId === $op->operation_key
                                    ? 'border-primary bg-primary/10 ring-2 ring-primary/20'
                                    : 'border-base-200 hover:border-primary/30 hover:bg-primary/5' }}">
                            <x-mary-icon name="o-cog-6-tooth"
                                class="w-5 h-5 shrink-0 {{ $operationId === $op->operation_key ? 'text-primary' : 'text-gray-400' }}" />
                            <span class="font-semibold truncate {{ $operationId === $op->operation_key ? 'text-primary' : '' }}">
                                {{ $op->operation_name ?: $op->operation_key }}
                            </span>
                            @if($operationId === $op->operation_key)
                                <x-mary-icon name="o-check-circle" class="w-5 h-5 text-primary shrink-0 ml-auto" />
                            @endif
                        </button>
                    @endforeach
                </div>
            @elseif(! $machineId)
                <div class="text-sm text-gray-400 py-4 text-center">Nejdříve vyberte stroj.</div>
            @else
                <x-mary-input wire:model="operationId" placeholder="Zadejte klíč operace" />
            @endif
        </x-mary-card>

        <x-mary-card title="7. Čas a detaily" shadow separator>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-mary-input label="Začátek" type="datetime-local" wire:model="startedAt" />
                    @error('startedAt') <span class="text-error text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <x-mary-input label="Konec" type="datetime-local" wire:model="endedAt" />
                    @error('endedAt') <span class="text-error text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                <div>
                    <x-mary-input label="Množství (ks)" type="number" wire:model="quantity" min="0" />
                    @error('quantity') <span class="text-error text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-4">
                <x-mary-textarea label="Poznámka" wire:model="notes" rows="2" />
            </div>
        </x-mary-card>

        <div class="flex justify-end gap-3 pb-8">
            <x-mary-button label="Zrušit" icon="o-x-mark" link="{{ route('vedouci.show', $operator->klic_subjektu) }}" />
            <x-mary-button
                :label="$editRecordId ? 'Uložit změny' : 'Přidat záznam'"
                icon="o-check"
                class="btn-primary"
                wire:click="saveRecord"
                spinner="saveRecord"
            />
        </div>
    </div>
</div>
