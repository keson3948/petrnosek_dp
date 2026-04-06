<div>
    <x-mary-header title="Přehled strojů" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-mary-input icon="o-magnifying-glass" wire:model.live.debounce.300ms="search" placeholder="Hledat stroj..." clearable />
        </x-slot:middle>
        <x-slot:actions>
            <x-mary-button label="Operátoři" icon="o-users" link="{{ route('vedouci.index') }}" />
        </x-slot:actions>
    </x-mary-header>

    {{-- Tabs: haly --}}
    @if($hallTabs->count() > 1)
        <div class="tabs tabs-bordered mb-4">
            <button wire:click="setTab('all')" class="tab {{ $activeTab === 'all' ? 'tab-active' : '' }}">
                Vše
            </button>
            @foreach($hallTabs as $hall)
                <button wire:click="setTab('{{ $hall }}')" class="tab {{ $activeTab === $hall ? 'tab-active' : '' }}">
                    {{ $hall }}
                </button>
            @endforeach
        </div>
    @endif

    <x-mary-card>
        <x-mary-table :headers="$headers" :rows="$rows" striped link="/vedouci/stroje/{key}">

            @scope('cell_name', $machine)
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 rounded-full shrink-0 {{ $machine->is_active ? ($machine->status_label === 'Pauza' ? 'bg-warning' : 'bg-success animate-pulse') : 'bg-base-300' }}"></div>
                    <div>
                        <span class="font-semibold">{{ $machine->name }}</span>
                        @if($machine->pracoviste)
                            <div class="text-xs text-gray-400">{{ $machine->pracoviste }}</div>
                        @endif
                    </div>
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
                    @if($machine->active_user_klic)
                        <a href="{{ route('vedouci.show', $machine->active_user_klic) }}" class="font-semibold hover:underline hover:text-primary">{{ $machine->active_user }}</a>
                    @else
                        <span class="font-semibold">{{ $machine->active_user }}</span>
                    @endif
                @else
                    <span class="text-gray-300">—</span>
                @endif
            @endscope

            @scope('cell_active_vp', $machine)
                @if($machine->active_vp)
                    @if($machine->active_vp_sys_klic)
                        <a href="{{ route('vp.show', $machine->active_vp_sys_klic) }}" class="font-mono text-sm hover:underline hover:text-primary">{{ $machine->active_vp }}</a>
                    @else
                        <span class="font-mono text-sm">{{ $machine->active_vp }}</span>
                    @endif
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
