<div>
    @if($activeRecord)
        <x-mary-card title="Aktuální výrobní operace" class="{{ $activeRecord->status === 'in_progress' ? 'border-primary' : 'border-warning' }}">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <div><strong>Zakázka:</strong> {{ $activeRecord->order_number }}</div>
                    @if($activeRecord->vp_number)
                        <div><strong>VP:</strong> {{ $activeRecord->vp_number }}</div>
                    @endif
                    <div><strong>Operace:</strong> {{ $activeRecord->operation_id }}</div>
                    @if($activeRecord->drawing_number)
                        <div><strong>Výkres:</strong> {{ $activeRecord->drawing_number }}</div>
                    @endif
                    @if($activeRecord->machine_id)
                        <div><strong>Stroj:</strong> {{ $activeRecord->machine_id }}</div>
                    @endif
                </div>
                <div class="text-right">
                    <div class="text-lg">
                        <strong>Stav:</strong>
                        @if($activeRecord->status === 'in_progress')
                            <span class="text-primary font-bold flex items-center justify-end gap-2">
                                <span class="relative flex h-3 w-3">
                                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                                  <span class="relative inline-flex rounded-full h-3 w-3 bg-primary"></span>
                                </span>
                                Probíhá
                            </span>
                        @else
                            <span class="text-warning font-bold">Pozastaveno</span>
                        @endif
                    </div>
                    <div class="text-sm mt-1 text-gray-500">Zahájeno: {{ $activeRecord->started_at->format('H:i') }}</div>
                </div>
            </div>

            <x-slot:actions>
                @if($activeRecord->status === 'in_progress')
                    <x-mary-button label="Pozastavit" icon="o-pause" wire:click="pauseOperation" class="btn-warning btn-outline" spinner="pauseOperation" />
                @else
                    <x-mary-button label="Obnovit" icon="o-play" wire:click="resumeOperation" class="btn-primary" spinner="resumeOperation" />
                @endif
                <x-mary-button label="Ukončit operaci" icon="o-check" wire:click="openCompleteModal" class="btn-success text-white" />
            </x-slot:actions>
        </x-mary-card>
    @else
        <x-mary-card title="Výrobní operace" class="bg-white">
            <p class="text-gray-500 mb-4">Momentálně nemáte aktivní žádnou výrobní operaci.</p>
            <x-mary-button label="Začít novou operaci" icon="o-play" wire:click="openStartModal" class="btn-primary" />
        </x-mary-card>
    @endif

    <!-- HISTORY SECTION -->
    <div class="mt-8">
        <x-mary-card title="Dnešní směna" class="bg-transparent border-0 shadow-none !p-0">
            @forelse($today as $record)
                <x-mary-collapse class="mb-2 border border-base-200 bg-white">
                    <x-slot:heading>
                        <div class="flex items-center justify-between w-full">
                            <div class="flex items-center gap-4 w-1/4">
                                <x-mary-avatar placeholder="??" class="bg-success text-white !w-10 !h-10" />
                                <div>
                                    <span class="font-bold" x-data x-tooltip="Výrobní příkaz: {{ $record->vp_number ?? $record->order_number }}">{{ $record->order_number }}</span>
                                    @if($record->vp_number)
                                        <div class="text-xs text-gray-500">VP: {{ $record->vp_number }}</div>
                                    @endif
                                    <div class="text-xs text-gray-500">Výkres: {{ $record->drawing_number ?? 'N/A' }}</div>
                                </div>
                            </div>
                            <div class="flex-1 px-4">
                                <div class="flex justify-between text-xs mb-1">
                                    <span>Hotovo: {{ $record->processed_quantity }} ks</span>
                                    <span>
                                        @if($record->worked_minutes !== null)
                                            Odpracováno: {{ intdiv($record->worked_minutes, 60) }}h {{ $record->worked_minutes % 60 }}min
                                        @else
                                            Zahájeno: {{ $record->started_at->format('H:i') }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <div class="w-1/6 flex justify-end items-center gap-2">
                                <x-mary-icon name="o-check-circle" class="text-success w-8 h-8" tooltip="Dokončeno" />
                            </div>
                        </div>
                    </x-slot:heading>
                    <x-slot:content>
                        <div class="p-4 border-t bg-base-50">
                            {{-- Editable fields with pencil icons --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                {{-- Zakázka --}}
                                <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-base-200">
                                    <div>
                                        <div class="text-xs text-gray-400 uppercase tracking-wide">Zakázka</div>
                                        <div class="font-semibold text-lg">{{ $record->order_number }}</div>
                                    </div>
                                    <button wire:click="openEditOrder({{ $record->id }})" class="btn btn-ghost btn-sm btn-square" title="Upravit zakázku">
                                        <x-mary-icon name="o-pencil" class="w-5 h-5 text-primary" />
                                    </button>
                                </div>

                                {{-- VP --}}
                                <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-base-200">
                                    <div>
                                        <div class="text-xs text-gray-400 uppercase tracking-wide">Výrobní příkaz (VP)</div>
                                        <div class="font-semibold text-lg">{{ $record->vp_number ?? '—' }}</div>
                                    </div>
                                    <button wire:click="openEditVp({{ $record->id }})" class="btn btn-ghost btn-sm btn-square" title="Upravit VP">
                                        <x-mary-icon name="o-pencil" class="w-5 h-5 text-primary" />
                                    </button>
                                </div>

                                {{-- Stroj + Operace --}}
                                <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-base-200">
                                    <div>
                                        <div class="text-xs text-gray-400 uppercase tracking-wide">Stroj / Operace</div>
                                        <div class="font-semibold">{{ $record->machine_id ?? 'Nezadáno' }} <span class="text-gray-400 mx-1">/</span> {{ $record->operation_id }}</div>
                                    </div>
                                    <button wire:click="openEditMachineOp({{ $record->id }})" class="btn btn-ghost btn-sm btn-square" title="Upravit stroj a operaci">
                                        <x-mary-icon name="o-pencil" class="w-5 h-5 text-primary" />
                                    </button>
                                </div>

                                {{-- Čas --}}
                                <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-base-200">
                                    <div>
                                        <div class="text-xs text-gray-400 uppercase tracking-wide">Odpracovaný čas</div>
                                        <div class="font-semibold text-lg">
                                            @if($record->worked_minutes !== null)
                                                {{ intdiv($record->worked_minutes, 60) }}h {{ $record->worked_minutes % 60 }}min
                                            @else
                                                {{ $record->started_at->format('H:i') }} - {{ $record->ended_at?->format('H:i') ?? '?' }}
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-400">
                                            {{ $record->started_at->format('d.m.Y H:i') }} - {{ $record->ended_at?->format('H:i') ?? '?' }}
                                            @if($record->total_paused_seconds > 0)
                                                (Pauza: {{ gmdate("H:i:s", $record->total_paused_seconds) }})
                                            @endif
                                        </div>
                                    </div>
                                    <button wire:click="openEditTime({{ $record->id }})" class="btn btn-ghost btn-sm btn-square" title="Upravit čas">
                                        <x-mary-icon name="o-pencil" class="w-5 h-5 text-primary" />
                                    </button>
                                </div>
                            </div>

                            {{-- Additional info --}}
                            <div class="mt-3 grid grid-cols-2 md:grid-cols-4 gap-3">
                                <div><strong>Operátor:</strong> {{ auth()->user()->name }}</div>
                                <div><strong>Množství:</strong> {{ $record->processed_quantity }} ks</div>
                            </div>

                            @if($record->notes)
                                <div class="mt-2">
                                    <strong>Poznámka:</strong> <span class="text-gray-600">{{ $record->notes }}</span>
                                </div>
                            @endif
                        </div>
                    </x-slot:content>
                </x-mary-collapse>
            @empty
                <div class="text-center py-6 text-gray-500 bg-white rounded-lg border border-dashed">
                    Dnes zatím nemáte žádné dokončené operace.
                </div>
            @endforelse
        </x-mary-card>

        @if($historical->count() > 0)
            <x-mary-card title="Historie (Posledních 5 dní)" class="bg-transparent border-0 shadow-none !p-0 mt-6">
                @foreach($historical as $record)
                    <x-mary-collapse class="bg-base-200 opacity-80 mb-2 border border-base-300">
                        <x-slot:heading>
                            <div class="flex items-center justify-between w-full grayscale-[50%]">
                                <div class="flex items-center gap-4 w-1/4">
                                    <x-mary-avatar placeholder="??" class="bg-neutral text-white !w-10 !h-10" />
                                    <div>
                                        <span class="font-bold">{{ $record->order_number }}</span>
                                        @if($record->vp_number)
                                            <div class="text-xs text-gray-500">VP: {{ $record->vp_number }}</div>
                                        @endif
                                        <div class="text-xs text-gray-500">Výkres: {{ $record->drawing_number ?? 'N/A' }}</div>
                                    </div>
                                </div>
                                <div class="flex-1 px-4">
                                    <div class="flex justify-between text-xs mb-1">
                                        <span>Hotovo: {{ $record->processed_quantity }} ks</span>
                                        <span>
                                            @if($record->worked_minutes !== null)
                                                Odpracováno: {{ intdiv($record->worked_minutes, 60) }}h {{ $record->worked_minutes % 60 }}min
                                            @else
                                                Dne: {{ $record->started_at->format('d.m.Y H:i') }}
                                            @endif
                                        </span>
                                    </div>
                                    <x-mary-progress class="progress-neutral h-1" value="100" max="100" />
                                </div>
                                <div class="w-1/6 flex justify-end items-center gap-2">
                                    <x-mary-badge value="Vyřešeno" class="badge-neutral badge-sm" />
                                </div>
                            </div>
                        </x-slot:heading>
                        <x-slot:content>
                            <div class="p-4 border-t border-base-300">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    {{-- Zakázka --}}
                                    <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-base-200">
                                        <div>
                                            <div class="text-xs text-gray-400 uppercase tracking-wide">Zakázka</div>
                                            <div class="font-semibold">{{ $record->order_number }}</div>
                                        </div>
                                        <button wire:click="openEditOrder({{ $record->id }})" class="btn btn-ghost btn-sm btn-square">
                                            <x-mary-icon name="o-pencil" class="w-5 h-5 text-primary" />
                                        </button>
                                    </div>

                                    {{-- VP --}}
                                    <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-base-200">
                                        <div>
                                            <div class="text-xs text-gray-400 uppercase tracking-wide">VP</div>
                                            <div class="font-semibold">{{ $record->vp_number ?? '—' }}</div>
                                        </div>
                                        <button wire:click="openEditVp({{ $record->id }})" class="btn btn-ghost btn-sm btn-square">
                                            <x-mary-icon name="o-pencil" class="w-5 h-5 text-primary" />
                                        </button>
                                    </div>

                                    {{-- Stroj + Operace --}}
                                    <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-base-200">
                                        <div>
                                            <div class="text-xs text-gray-400 uppercase tracking-wide">Stroj / Operace</div>
                                            <div class="font-semibold">{{ $record->machine_id ?? '—' }} / {{ $record->operation_id }}</div>
                                        </div>
                                        <button wire:click="openEditMachineOp({{ $record->id }})" class="btn btn-ghost btn-sm btn-square">
                                            <x-mary-icon name="o-pencil" class="w-5 h-5 text-primary" />
                                        </button>
                                    </div>

                                    {{-- Čas --}}
                                    <div class="flex items-center justify-between p-3 rounded-lg bg-white border border-base-200">
                                        <div>
                                            <div class="text-xs text-gray-400 uppercase tracking-wide">Odpracovaný čas</div>
                                            <div class="font-semibold">
                                                @if($record->worked_minutes !== null)
                                                    {{ intdiv($record->worked_minutes, 60) }}h {{ $record->worked_minutes % 60 }}min
                                                @else
                                                    {{ $record->started_at->format('H:i') }} - {{ $record->ended_at?->format('H:i') ?? '?' }}
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-400">
                                                {{ $record->started_at->format('d.m.Y H:i') }} - {{ $record->ended_at?->format('H:i') ?? '?' }}
                                            </div>
                                        </div>
                                        <button wire:click="openEditTime({{ $record->id }})" class="btn btn-ghost btn-sm btn-square">
                                            <x-mary-icon name="o-pencil" class="w-5 h-5 text-primary" />
                                        </button>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <strong>Operátor:</strong> {{ auth()->user()->name }}
                                    <span class="mx-2">|</span>
                                    <strong>Množství:</strong> {{ $record->processed_quantity }} ks
                                </div>

                                @if($record->notes)
                                    <div class="mt-2">
                                        <strong>Poznámka:</strong> <span class="text-gray-600">{{ $record->notes }}</span>
                                    </div>
                                @endif
                            </div>
                        </x-slot:content>
                    </x-mary-collapse>
                @endforeach
            </x-mary-card>
        @endif
    </div>

    <x-mary-modal wire:model="showStartModal" title="Nová operace" separator>
        <x-mary-form wire:submit="startOperation">
            <x-mary-input label="Číslo zakázky *" wire:model="order_number" placeholder="Např. 2026/001" />
            <x-mary-input label="Číslo VP (volitelné)" wire:model="vp_number" placeholder="Výrobní příkaz" />
            <x-mary-input label="Výrobní operace *" wire:model="operation_id" placeholder="Např. Řezání" />
            <x-mary-input label="Číslo výkresu (volitelné)" wire:model="drawing_number" />
            <x-mary-input label="Stroj (volitelné)" wire:model="machine_id" />

            <x-slot:actions>
                <x-mary-button label="Zrušit" @click="$wire.showStartModal = false" />
                <x-mary-button label="Zahájit" class="btn-primary" type="submit" spinner="startOperation" />
            </x-slot:actions>
        </x-mary-form>
    </x-mary-modal>

    <x-mary-modal wire:model="showCompleteModal" title="Dokončení operace" separator>
        <x-mary-form wire:submit="completeOperation">
            <x-mary-input label="Množství zpracovaných jednotek *" type="number" wire:model.defer="processed_quantity" min="0" />
            <x-mary-textarea label="Poznámka / Problémy (volitelné)" wire:model.defer="notes" rows="4" />

            <x-slot:actions>
                <x-mary-button label="Zrušit" @click="$wire.showCompleteModal = false" />
                <x-mary-button label="Ukončit operaci a uložit" class="btn-success text-white" type="submit" spinner="completeOperation" />
            </x-slot:actions>
        </x-mary-form>
    </x-mary-modal>

    <x-mary-modal wire:model="showEditOrderModal" title="{{ $showOrderListInline && $orderListTarget === 'order' ? 'Vybrat zakázku ze seznamu' : 'Upravit číslo zakázky' }}" separator>
        @if($showOrderListInline && $orderListTarget === 'order')
            {{-- Inline order list view --}}
            <div class="mb-4">
                <x-mary-input placeholder="Hledat..." wire:model.live.debounce.300ms="orderListSearch" icon="o-magnifying-glass" clearable />
            </div>

            <div class="overflow-y-auto max-h-96">
                <table class="table table-sm w-full">
                    <thead>
                        <tr>
                            <th>Klíč</th>
                            <th>Název</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->orderList as $doklad)
                            <tr class="hover:bg-base-200 cursor-pointer" wire:click="selectOrder('{{ $doklad->KlicDokla }}', '{{ addslashes($doklad->Nazev1 ?? '') }}')">
                                <td class="font-mono font-bold">{{ $doklad->KlicDokla }}</td>
                                <td>{{ $doklad->Nazev1 ?? '' }}</td>
                                <td class="text-right">
                                    <x-mary-icon name="o-arrow-right" class="w-4 h-4 text-primary" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-gray-400 py-8">Žádné doklady nenalezeny.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($this->orderList->hasPages())
                <div class="mt-4 border-t pt-4">
                    {{ $this->orderList->links() }}
                </div>
            @endif
        @else
            <div class="flex gap-2 items-end">
                <div class="flex-1">
                    <x-mary-input label="Číslo zakázky" wire:model="edit_order_number" placeholder="Zadejte číslo zakázky" />
                </div>
                <button type="button" wire:click="openOrderList('order')" class="btn btn-outline btn-primary btn-square mb-1" title="Vybrat ze seznamu">
                    <x-mary-icon name="o-queue-list" class="w-5 h-5" />
                </button>
            </div>
        @endif

        <x-slot:actions>
            @if($showOrderListInline && $orderListTarget === 'order')
                <x-mary-button label="Zpět" icon="o-arrow-left" wire:click="closeOrderList" />
            @else
                <x-mary-button label="Zrušit" @click="$wire.showEditOrderModal = false" />
                <x-mary-button label="Uložit" class="btn-primary" wire:click="saveEditOrder" spinner="saveEditOrder" />
            @endif
        </x-slot:actions>
    </x-mary-modal>

    <x-mary-modal wire:model="showEditVpModal" title="{{ $showOrderListInline && $orderListTarget === 'vp' ? 'Vybrat VP ze seznamu' : 'Upravit číslo VP' }}" separator>
        @if($showOrderListInline && $orderListTarget === 'vp')
            {{-- Inline order list view --}}
            <div class="mb-4">
                <x-mary-input placeholder="Hledat..." wire:model.live.debounce.300ms="orderListSearch" icon="o-magnifying-glass" clearable />
            </div>

            <div class="overflow-y-auto max-h-96">
                <table class="table table-sm w-full">
                    <thead>
                        <tr>
                            <th>Klíč</th>
                            <th>Název</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->orderList as $doklad)
                            <tr class="hover:bg-base-200 cursor-pointer" wire:click="selectOrder('{{ $doklad->KlicDokla }}', '{{ addslashes($doklad->Nazev1 ?? '') }}')">
                                <td class="font-mono font-bold">{{ $doklad->KlicDokla }}</td>
                                <td>{{ $doklad->Nazev1 ?? '' }}</td>
                                <td class="text-right">
                                    <x-mary-icon name="o-arrow-right" class="w-4 h-4 text-primary" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-gray-400 py-8">Žádné doklady nenalezeny.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($this->orderList->hasPages())
                <div class="mt-4 border-t pt-4">
                    {{ $this->orderList->links() }}
                </div>
            @endif
        @else
            {{-- Edit form view --}}
            <div class="flex gap-2 items-end">
                <div class="flex-1">
                    <x-mary-input label="Číslo VP" wire:model="edit_vp_number" placeholder="Zadejte číslo výrobního příkazu" />
                </div>
                <button type="button" wire:click="openOrderList('vp')" class="btn btn-outline btn-primary btn-square mb-1" title="Vybrat ze seznamu">
                    <x-mary-icon name="o-queue-list" class="w-5 h-5" />
                </button>
            </div>
        @endif

        <x-slot:actions>
            @if($showOrderListInline && $orderListTarget === 'vp')
                <x-mary-button label="Zpět" icon="o-arrow-left" wire:click="closeOrderList" />
            @else
                <x-mary-button label="Zrušit" @click="$wire.showEditVpModal = false" />
                <x-mary-button label="Uložit" class="btn-primary" wire:click="saveEditVp" spinner="saveEditVp" />
            @endif
        </x-slot:actions>
    </x-mary-modal>

    <x-mary-modal wire:model="showEditMachineOpModal" title="Upravit stroj a operaci" separator>
        <x-mary-form wire:submit="saveEditMachineOp">
            {{-- Machine selection --}}
            <div class="mb-4">
                <label class="label"><span class="label-text font-semibold">Stroj</span></label>
                @if($this->userMachines->count() > 0)
                    <div class="grid grid-cols-1 gap-2 mt-1">
                        @foreach($this->userMachines as $machine)
                            <button type="button"
                                wire:click="selectMachine('{{ $machine->machine_key }}', '{{ addslashes($machine->machine_name ?? $machine->machine_key) }}')"
                                class="btn btn-block justify-start text-left {{ $edit_machine_id === $machine->machine_key ? 'btn-primary' : 'btn-outline' }}">
                                <x-mary-icon name="o-wrench-screwdriver" class="w-5 h-5 mr-2" />
                                {{ $machine->machine_name ?? $machine->machine_key }}
                            </button>
                        @endforeach
                    </div>
                @else
                    <div class="text-gray-400 text-sm py-2">Nemáte přiřazeny žádné stroje. Kontaktujte správce.</div>
                    <x-mary-input wire:model="edit_machine_id" placeholder="Zadejte ručně" class="mt-2" />
                @endif
            </div>

            {{-- Operation selection (filtered by machine) --}}
            <div class="mb-4">
                <label class="label"><span class="label-text font-semibold">Operace (úkon)</span></label>
                @if($edit_machine_id && $this->machineOperations->count() > 0)
                    <div class="grid grid-cols-1 gap-2 mt-1">
                        @foreach($this->machineOperations as $op)
                            <button type="button"
                                wire:click="selectOperation('{{ $op->operation_key }}')"
                                class="btn btn-block justify-start text-left {{ $edit_operation_id === $op->operation_key ? 'btn-primary' : 'btn-outline' }}">
                                <x-mary-icon name="o-cog-6-tooth" class="w-5 h-5 mr-2" />
                                {{ $op->operation_name ?? $op->operation_key }}
                            </button>
                        @endforeach
                    </div>
                @elseif($edit_machine_id)
                    <div class="text-gray-400 text-sm py-2">Pro tento stroj nejsou definovány operace.</div>
                    <x-mary-input wire:model="edit_operation_id" placeholder="Zadejte ručně" class="mt-2" />
                @else
                    <div class="text-gray-400 text-sm py-2">Nejprve vyberte stroj.</div>
                    <x-mary-input wire:model="edit_operation_id" placeholder="Nebo zadejte ručně" class="mt-2" />
                @endif
            </div>

            <x-slot:actions>
                <x-mary-button label="Zrušit" @click="$wire.showEditMachineOpModal = false" />
                <x-mary-button label="Uložit" class="btn-primary" type="submit" spinner="saveEditMachineOp" />
            </x-slot:actions>
        </x-mary-form>
    </x-mary-modal>

    <x-mary-modal wire:model="showEditTimeModal" title="Upravit odpracovaný čas" separator>
        <x-mary-form wire:submit="saveEditTime">
            {{-- Start time --}}
            <x-mary-input label="Začátek" type="datetime-local" wire:model="edit_started_at" />

            {{-- Touch-friendly duration editor --}}
            <label class="label"><span class="label-text font-semibold">Odpracovaná doba</span></label>
            <div class="flex items-center justify-center gap-6 py-4">
                {{-- Hours --}}
                <div class="flex flex-col items-center">
                    <span class="text-xs text-gray-400 uppercase mb-2">Hodiny</span>
                    <x-mary-button icon="o-plus" wire:click="adjustHours(1)"></x-mary-button>
                    <div class="text-5xl font-bold my-3 w-20 text-center tabular-nums">
                        {{ str_pad($edit_hours, 2, '0', STR_PAD_LEFT) }}
                    </div>
                    <x-mary-button icon="o-minus" wire:click="adjustHours(-1)"></x-mary-button>
                </div>

                <div class="text-5xl font-bold text-gray-300 mt-6">:</div>

                {{-- Minutes --}}
                <div class="flex flex-col items-center">
                    <span class="text-xs text-gray-400 uppercase mb-2">Minuty</span>
                    <div class="flex gap-2">
                        <x-mary-button wire:click="adjustMinutes(10)">
                            +10
                        </x-mary-button>
                        <x-mary-button type="button" wire:click="adjustMinutes(1)">
                            +1
                        </x-mary-button>
                    </div>
                    <div class="text-5xl font-bold my-3 w-20 text-center tabular-nums">
                        {{ str_pad($edit_minutes, 2, '0', STR_PAD_LEFT) }}
                    </div>
                    <div class="flex gap-2">
                        <x-mary-button type="button" wire:click="adjustMinutes(-10)">
                            -10
                        </x-mary-button>
                        <x-mary-button type="button" wire:click="adjustMinutes(-1)">
                            -1
                        </x-mary-button>
                    </div>
                </div>
            </div>

            <div class="text-center text-sm text-gray-500 mt-2">
                Celkem: {{ $edit_hours }} hodin {{ $edit_minutes }} minut
            </div>

            <x-slot:actions>
                <x-mary-button label="Zrušit" @click="$wire.showEditTimeModal = false" />
                <x-mary-button label="Uložit" class="btn-primary" type="submit" spinner="saveEditTime" />
            </x-slot:actions>
        </x-mary-form>
    </x-mary-modal>
</div>
