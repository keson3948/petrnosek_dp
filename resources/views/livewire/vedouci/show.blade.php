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

    {{-- Filters --}}
    <div class="flex flex-wrap items-end gap-4 mb-6">
        <x-mary-input label="Od" type="date" wire:model.live="dateFrom" class="w-40" />
        <x-mary-input label="Do" type="date" wire:model.live="dateTo" class="w-40" />
    </div>

    {{-- Records Table --}}
    <x-mary-card>
        @if($this->records->isEmpty())
            <div class="text-center py-8 text-gray-500">
                Žádné záznamy v tomto období.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="table table-sm w-full">
                    <thead>
                        <tr>
                            <th>Datum</th>
                            <th>VP</th>
                            <th>Stroj</th>
                            <th>Operace</th>
                            <th>Množství</th>
                            <th>Čas</th>
                            <th>Poznámka</th>
                            <th class="text-right">Akce</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($this->records as $record)
                            @php $info = $this->getRecordInfo($record); @endphp
                            <tr class="hover">
                                <td class="whitespace-nowrap">{{ $record->started_at?->format('d.m.Y') }}</td>
                                <td class="font-mono">{{ trim($record->doklad?->KlicDokla ?? '') ?: '—' }}</td>
                                <td>{{ trim($record->machine?->NazevUplny ?? $record->machine_id ?? '') ?: '—' }}</td>
                                <td>{{ trim($record->operation?->Nazev1 ?? $record->operation_id) }}</td>
                                <td>{{ $record->processed_quantity ?? 0 }} ks</td>
                                <td class="whitespace-nowrap tabular-nums">
                                    @if($info['workedH'] !== null)
                                        {{ $info['workedH'] }}:{{ str_pad($info['workedM'], 2, '0', STR_PAD_LEFT) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="max-w-48 truncate">{{ $record->notes ?: '—' }}</td>
                                <td class="text-right whitespace-nowrap">
                                    <x-mary-button icon="o-pencil" wire:click="openEditModal({{ $record->ID }})" class="btn-ghost btn-xs" spinner />
                                    <x-mary-button icon="o-trash" wire:click="deleteRecord({{ $record->ID }})" wire:confirm="Opravdu smazat tento záznam?" class="btn-ghost btn-xs text-error" spinner />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

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
        @endif
    </x-mary-card>

    {{-- ====== MODAL: Přidat záznam ====== --}}
    <x-mary-modal wire:model="showAddModal" title="Přidat pracovní záznam" separator box-class="max-w-2xl">
        <div class="flex flex-col gap-4">
            {{-- VP --}}
            <div>
                <label class="label"><span class="label-text font-semibold">Výrobní příkaz</span></label>
                @if($add_sysPrimKlic)
                    <div class="p-3 border-2 border-primary bg-primary/10 rounded-lg flex items-center justify-between">
                        <div class="font-bold font-mono text-primary">{{ $add_vpLabel }}</div>
                        <x-mary-button icon="o-x-mark" wire:click="addClearVp" class="btn-ghost btn-xs" />
                    </div>
                @else
                    <x-mary-input wire:model.live.debounce.300ms="add_vpSearch" placeholder="Hledat VP..." icon="o-magnifying-glass" clearable />
                    @if($this->addVpSearchResults->count() > 0)
                        <div class="mt-2 space-y-1 max-h-48 overflow-y-auto">
                            @foreach($this->addVpSearchResults as $doklad)
                                <button type="button"
                                    wire:click="addSelectVp('{{ addslashes(trim($doklad->SysPrimKlicDokladu)) }}', '{{ addslashes(trim($doklad->KlicDokla)) }}')"
                                    class="w-full text-left p-2 border rounded hover:border-primary/50 transition-colors">
                                    <span class="font-mono font-bold">{{ trim($doklad->KlicDokla) }}</span>
                                    @if(trim($doklad->MPSProjekt ?? ''))
                                        <span class="text-sm text-gray-500 ml-2">{{ trim($doklad->MPSProjekt) }}</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>

            {{-- Stroj --}}
            <div>
                <label class="label"><span class="label-text font-semibold">Stroj</span></label>
                <div class="flex flex-wrap gap-2">
                    @foreach($this->operatorMachines as $machine)
                        <button type="button"
                            wire:click="addSelectMachine('{{ $machine->machine_key }}')"
                            class="btn btn-sm {{ $add_machine_id === $machine->machine_key ? 'btn-secondary' : 'btn-outline' }}">
                            {{ $machine->machine_name ?: $machine->machine_key }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Operace --}}
            <div>
                <label class="label"><span class="label-text font-semibold">Operace</span></label>
                @if($add_machine_id && $this->addMachineOperations->count() > 0)
                    <div class="flex flex-wrap gap-2">
                        @foreach($this->addMachineOperations as $op)
                            <button type="button"
                                wire:click="$set('add_operation_id', '{{ $op->operation_key }}')"
                                class="btn btn-sm {{ $add_operation_id === $op->operation_key ? 'btn-primary' : 'btn-outline' }}">
                                {{ $op->operation_name ?: $op->operation_key }}
                            </button>
                        @endforeach
                    </div>
                @else
                    <div class="text-sm text-gray-400">Vyberte stroj.</div>
                @endif
                @error('add_operation_id') <span class="text-error text-sm">{{ $message }}</span> @enderror
            </div>

            {{-- Čas --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <x-mary-input label="Začátek" type="datetime-local" wire:model="add_startedAt" />
                    @error('add_startedAt') <span class="text-error text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <x-mary-input label="Hodiny" type="number" wire:model="add_hours" min="0" />
                </div>
                <div>
                    <x-mary-input label="Minuty" type="number" wire:model="add_minutes" min="0" max="59" />
                </div>
            </div>
            @error('add_time') <span class="text-error text-sm">{{ $message }}</span> @enderror

            {{-- Množství --}}
            <x-mary-input label="Množství (ks)" type="number" wire:model="add_quantity" min="0" />
            @error('add_quantity') <span class="text-error text-sm">{{ $message }}</span> @enderror

            {{-- Poznámka --}}
            <x-mary-textarea label="Poznámka" wire:model="add_notes" rows="2" />
        </div>

        <x-slot:actions>
            <x-mary-button label="Zrušit" @click="$wire.showAddModal = false" />
            <x-mary-button label="Přidat" class="btn-primary" wire:click="saveAddRecord" spinner="saveAddRecord" />
        </x-slot:actions>
    </x-mary-modal>

    {{-- ====== MODAL: Upravit záznam ====== --}}
    <x-mary-modal wire:model="showEditModal" title="Upravit pracovní záznam" separator box-class="max-w-2xl">
        <div class="flex flex-col gap-4">
            {{-- VP --}}
            <div>
                <label class="label"><span class="label-text font-semibold">Výrobní příkaz</span></label>
                @if($edit_sysPrimKlic)
                    <div class="p-3 border-2 border-primary bg-primary/10 rounded-lg flex items-center justify-between">
                        <div class="font-bold font-mono text-primary">{{ $edit_vpLabel }}</div>
                        <x-mary-button icon="o-x-mark" wire:click="editClearVp" class="btn-ghost btn-xs" />
                    </div>
                @else
                    <x-mary-input wire:model.live.debounce.300ms="edit_vpSearch" placeholder="Hledat VP..." icon="o-magnifying-glass" clearable />
                    @if($this->editVpSearchResults->count() > 0)
                        <div class="mt-2 space-y-1 max-h-48 overflow-y-auto">
                            @foreach($this->editVpSearchResults as $doklad)
                                <button type="button"
                                    wire:click="editSelectVp('{{ addslashes(trim($doklad->SysPrimKlicDokladu)) }}', '{{ addslashes(trim($doklad->KlicDokla)) }}')"
                                    class="w-full text-left p-2 border rounded hover:border-primary/50 transition-colors">
                                    <span class="font-mono font-bold">{{ trim($doklad->KlicDokla) }}</span>
                                    @if(trim($doklad->MPSProjekt ?? ''))
                                        <span class="text-sm text-gray-500 ml-2">{{ trim($doklad->MPSProjekt) }}</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>

            {{-- Stroj --}}
            <div>
                <label class="label"><span class="label-text font-semibold">Stroj</span></label>
                <div class="flex flex-wrap gap-2">
                    @foreach($this->operatorMachines as $machine)
                        <button type="button"
                            wire:click="editSelectMachine('{{ $machine->machine_key }}')"
                            class="btn btn-sm {{ $edit_machine_id === $machine->machine_key ? 'btn-secondary' : 'btn-outline' }}">
                            {{ $machine->machine_name ?: $machine->machine_key }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Operace --}}
            <div>
                <label class="label"><span class="label-text font-semibold">Operace</span></label>
                @if($edit_machine_id && $this->editMachineOperations->count() > 0)
                    <div class="flex flex-wrap gap-2">
                        @foreach($this->editMachineOperations as $op)
                            <button type="button"
                                wire:click="$set('edit_operation_id', '{{ $op->operation_key }}')"
                                class="btn btn-sm {{ $edit_operation_id === $op->operation_key ? 'btn-primary' : 'btn-outline' }}">
                                {{ $op->operation_name ?: $op->operation_key }}
                            </button>
                        @endforeach
                    </div>
                @else
                    <div class="text-sm text-gray-400">Vyberte stroj.</div>
                @endif
                @error('edit_operation_id') <span class="text-error text-sm">{{ $message }}</span> @enderror
            </div>

            {{-- Čas --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <x-mary-input label="Začátek" type="datetime-local" wire:model="edit_startedAt" />
                    @error('edit_startedAt') <span class="text-error text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <x-mary-input label="Hodiny" type="number" wire:model="edit_hours" min="0" />
                </div>
                <div>
                    <x-mary-input label="Minuty" type="number" wire:model="edit_minutes" min="0" max="59" />
                </div>
            </div>
            @error('edit_time') <span class="text-error text-sm">{{ $message }}</span> @enderror

            {{-- Množství --}}
            <x-mary-input label="Množství (ks)" type="number" wire:model="edit_quantity" min="0" />
            @error('edit_quantity') <span class="text-error text-sm">{{ $message }}</span> @enderror

            {{-- Poznámka --}}
            <x-mary-textarea label="Poznámka" wire:model="edit_notes" rows="2" />
        </div>

        <x-slot:actions>
            <x-mary-button label="Zrušit" @click="$wire.showEditModal = false" />
            <x-mary-button label="Uložit" class="btn-primary" wire:click="saveEditRecord" spinner="saveEditRecord" />
        </x-slot:actions>
    </x-mary-modal>
</div>
