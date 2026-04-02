{{-- ====== MODAL: Upravit VP ====== --}}
<x-mary-modal wire:model="showEditVpModal" title="{{ $showOrderListInline ? 'Vybrat VP ze seznamu' : 'Upravit výrobní příkaz' }}" separator>
    @if($showOrderListInline)
        <div class="mb-4">
            <x-mary-input placeholder="Hledat..." wire:model.live.debounce.300ms="orderListSearch" icon="o-magnifying-glass" clearable />
        </div>
        <div class="overflow-y-auto max-h-96">
            <table class="table table-sm w-full">
                <thead><tr><th>Klíč</th><th>Název</th><th></th></tr></thead>
                <tbody>
                @forelse($this->orderList as $doklad)
                    <tr class="hover:bg-base-200 cursor-pointer" wire:click="selectOrder('{{ $doklad->KlicDokla }}', '{{ addslashes($doklad->Nazev1 ?? '') }}')">
                        <td class="font-mono font-bold">{{ $doklad->KlicDokla }}</td>
                        <td>{{ $doklad->Nazev1 ?? '' }}</td>
                        <td class="text-right"><x-mary-icon name="o-arrow-right" class="w-4 h-4 text-primary" /></td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-gray-400 py-8">Žádné doklady nenalezeny.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($this->orderList->hasPages())
            <div class="mt-4 border-t pt-4">{{ $this->orderList->links() }}</div>
        @endif
    @else
        <div class="flex gap-2 items-end">
            <div class="flex-1">
                <x-mary-input label="Číslo VP (KlicDokla)" wire:model="edit_klicDokla" placeholder="Zadejte číslo výrobního příkazu" />
            </div>
            <button type="button" wire:click="openOrderList('vp')" class="btn btn-outline btn-primary btn-square mb-1" title="Vybrat ze seznamu">
                <x-mary-icon name="o-queue-list" class="w-5 h-5" />
            </button>
        </div>
    @endif
    <x-slot:actions>
        @if($showOrderListInline)
            <x-mary-button label="Zpět" icon="o-arrow-left" wire:click="closeOrderList" />
        @else
            <x-mary-button label="Zrušit" @click="$wire.showEditVpModal = false" />
            <x-mary-button label="Uložit" class="btn-primary" wire:click="saveEditVp" spinner="saveEditVp" />
        @endif
    </x-slot:actions>
</x-mary-modal>

{{-- ====== MODAL: Upravit stroj a operaci ====== --}}
<x-mary-modal wire:model="showEditMachineOpModal" title="Upravit stroj a operaci" separator>
    <x-mary-form wire:submit="saveEditMachineOp">
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

{{-- ====== MODAL: Upravit čas ====== --}}
<x-mary-modal wire:model="showEditTimeModal" title="Upravit odpracovaný čas" separator>
    <x-mary-form wire:submit="saveEditTime">
        <x-mary-input label="Začátek" type="datetime-local" wire:model="edit_started_at" />

        <label class="label"><span class="label-text font-semibold">Odpracovaná doba</span></label>
        <div class="flex items-center justify-center gap-6 py-4">
            <div class="flex flex-col items-center">
                <span class="text-xs text-gray-400 uppercase mb-2">Hodiny</span>
                <x-mary-button icon="o-plus" wire:click="adjustHours(1)"></x-mary-button>
                <div class="text-5xl font-bold my-3 w-20 text-center tabular-nums">
                    {{ str_pad($edit_hours, 2, '0', STR_PAD_LEFT) }}
                </div>
                <x-mary-button icon="o-minus" wire:click="adjustHours(-1)"></x-mary-button>
            </div>

            <div class="text-5xl font-bold text-gray-300 mt-6">:</div>

            <div class="flex flex-col items-center">
                <span class="text-xs text-gray-400 uppercase mb-2">Minuty</span>
                <div class="flex gap-2">
                    <x-mary-button wire:click="adjustMinutes(10)">+10</x-mary-button>
                    <x-mary-button type="button" wire:click="adjustMinutes(1)">+1</x-mary-button>
                </div>
                <div class="text-5xl font-bold my-3 w-20 text-center tabular-nums">
                    {{ str_pad($edit_minutes, 2, '0', STR_PAD_LEFT) }}
                </div>
                <div class="flex gap-2">
                    <x-mary-button type="button" wire:click="adjustMinutes(-10)">-10</x-mary-button>
                    <x-mary-button type="button" wire:click="adjustMinutes(-1)">-1</x-mary-button>
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
