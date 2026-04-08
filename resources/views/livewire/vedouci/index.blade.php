<div>
    <x-mary-header title="Přehled operátorů" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-mary-input icon="o-magnifying-glass" wire:model.live.debounce.300ms="search" placeholder="Hledat operátora..." clearable />
        </x-slot:middle>
        <x-slot:actions>
            <x-mary-button label="Stroje" icon="o-wrench-screwdriver" link="{{ route('vedouci.machines') }}"/>
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card>
        <x-mary-table :headers="$headers" :rows="$users" :sort-by="$sortBy" with-pagination>

            @scope('cell_name', $user)
                <a href="{{ route('vedouci.show', $user->klic_subjektu) }}" class="flex items-center gap-3 hover:text-primary transition">
                    <span class="font-semibold">{{ $user->name }}</span>
                </a>
            @endscope

            @scope('cell_status_label', $user)
                @if($user->is_active)
                    <x-mary-badge :value="$user->status_label"
                        class="{{ $user->status_label === 'Pauza' ? 'badge-warning' : 'badge-success' }} badge-sm" />
                @else
                    <span class="text-gray-400 text-sm">Neaktivní</span>
                @endif
            @endscope

            @scope('cell_current_vp', $user)
                @if($user->current_vp)
                    @if($user->current_vp_sys_klic)
                        <a href="{{ route('vp.show', $user->current_vp_sys_klic) }}" class="font-mono text-sm hover:underline hover:text-primary">{{ $user->current_vp }}</a>
                    @else
                        <span class="font-mono text-sm">{{ $user->current_vp }}</span>
                    @endif
                @else
                    <span class="text-gray-300">—</span>
                @endif
            @endscope

            @scope('cell_current_operation', $user)
                @if($user->current_operation)
                    <span class="text-sm">{{ $user->current_operation }}</span>
                @else
                    <span class="text-gray-300">—</span>
                @endif
            @endscope

            @scope('cell_current_machine', $user)
                @if($user->current_machine)
                    <span class="text-sm">{{ $user->current_machine }}</span>
                @else
                    <span class="text-gray-300">—</span>
                @endif
            @endscope

            @scope('cell_started_at_label', $user)
                @if($user->started_at_label)
                    <span class="text-sm tabular-nums">{{ $user->started_at_label }}</span>
                @else
                    <span class="text-gray-300">—</span>
                @endif
            @endscope

        </x-mary-table>
    </x-mary-card>
</div>
