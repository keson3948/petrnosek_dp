<div>
    <x-mary-header title="Přehled strojů" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-mary-input icon="o-magnifying-glass" wire:model.live.debounce.300ms="search" placeholder="Hledat stroj..." clearable />
        </x-slot:middle>
        <x-slot:actions>
            <x-mary-button label="Operátoři" icon="o-users" link="{{ route('vedouci.index') }}" />
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card>
        <x-mary-table :headers="$headers" :rows="$rows" striped link="/vedouci/stroje/{key}">

            @scope('cell_name', $machine)
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 rounded-full shrink-0 {{ $machine->is_active ? ($machine->status_label === 'Pauza' ? 'bg-warning' : 'bg-success animate-pulse') : 'bg-base-300' }}"></div>
                    <span class="font-semibold">{{ $machine->name }}</span>
                </div>
            @endscope

            @scope('cell_status_label', $machine)
                @if($machine->is_active)
                    <x-mary-badge :value="$machine->status_label"
                        class="{{ $machine->status_label === 'Pauza' ? 'badge-warning' : 'badge-success' }} badge-sm" />
                @else
                    <span class="text-gray-400 text-sm">Volný</span>
                @endif
            @endscope

            @scope('cell_active_user', $machine)
                @if($machine->active_user)
                    <span class="font-semibold">{{ $machine->active_user }}</span>
                @else
                    <span class="text-gray-300">—</span>
                @endif
            @endscope

            @scope('cell_active_vp', $machine)
                @if($machine->active_vp)
                    <span class="font-mono text-sm">{{ $machine->active_vp }}</span>
                @else
                    <span class="text-gray-300">—</span>
                @endif
            @endscope

            @scope('cell_active_operation', $machine)
                @if($machine->active_operation)
                    <span class="text-sm">{{ $machine->active_operation }}</span>
                @else
                    <span class="text-gray-300">—</span>
                @endif
            @endscope

        </x-mary-table>
    </x-mary-card>
</div>
