<div>
    <x-mary-header title="Přehled zaměstnanců" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-mary-input icon="o-magnifying-glass" wire:model.live.debounce.300ms="search" placeholder="Hledat zaměstnance..." clearable />
        </x-slot:middle>
        <x-slot:actions>
            <x-mary-button label="Stroje" icon="o-wrench-screwdriver" link="{{ route('vedouci.machines') }}"/>
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card title="V práci ({{ $presentUsers->count() }})" class="mb-6">
        @if($presentUsers->isEmpty())
            <div class="text-gray-400 text-sm py-4">Nikdo není v práci.</div>
        @else
            <x-mary-table :headers="$presentHeaders" :rows="$presentUsers" :sort-by="$sortBy">

                @scope('cell_name', $user)
                    <a href="{{ route('vedouci.show', $user->klic_subjektu) }}" class="flex items-center gap-3 hover:text-primary transition">
                        <span class="font-semibold">{{ $user->name }}</span>
                    </a>
                @endscope

                @scope('cell_arrival', $user)
                    @if($user->arrival)
                        <span class="text-sm tabular-nums">{{ $user->arrival }}</span>
                    @else
                        <span class="text-gray-300">—</span>
                    @endif
                @endscope

                @scope('cell_current_vp', $user)
                    @if($user->current_vp)
                        @if($user->current_vp_sys_klic)
                            <a href="{{ route('vp.show', $user->current_vp_sys_klic) }}" class="flex items-center gap-3 hover:text-primary transition hover:text-primary">{{ $user->current_vp }}</a>
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
        @endif
    </x-mary-card>

    <x-mary-card title="Nepřítomní ({{ $absentUsers->count() }})">
        @if($absentUsers->isEmpty())
            <div class="text-gray-400 text-sm py-4">Všichni jsou v práci.</div>
        @else
            <x-mary-table :headers="$absentHeaders" :rows="$absentUsers" :sort-by="$sortBy">

                @scope('cell_name', $user)
                    <a href="{{ route('vedouci.show', $user->klic_subjektu) }}" class="flex items-center gap-3 hover:text-primary transition">
                        <span class="font-semibold">{{ $user->name }}</span>
                    </a>
                @endscope

                @scope('cell_worked_hours', $user)
                    @if($user->worked_hours)
                        <span class="text-sm tabular-nums font-semibold">{{ $user->worked_hours }}</span>
                    @else
                        <span class="text-gray-300">—</span>
                    @endif
                @endscope

            </x-mary-table>
        @endif
    </x-mary-card>
</div>
