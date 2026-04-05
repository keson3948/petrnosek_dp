<div>
    <x-mary-header title="Přehled operátorů" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-mary-input icon="o-magnifying-glass" wire:model.live.debounce.300ms="search" placeholder="Hledat operátora..." clearable />
        </x-slot:middle>
        <x-slot:actions>
            <x-mary-button label="Stroje" icon="o-wrench-screwdriver" link="{{ route('vedouci.machines') }}" class="btn-outline" />
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card>
        <x-mary-table :headers="$headers" :rows="$users" :sort-by="$sortBy" with-pagination link="/vedouci/operator/{klic_subjektu}">

            @scope('cell_name', $user)
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 rounded-full shrink-0 {{ $user->is_active ? ($user->status_label === 'Pauza' ? 'bg-warning' : 'bg-success animate-pulse') : 'bg-base-300' }}"></div>
                    <span class="font-semibold">{{ $user->name }}</span>
                </div>
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
                    <span class="font-mono text-sm">{{ $user->current_vp }}</span>
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
