<div>
    <x-mary-header title="Stroje" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-mary-input icon="o-magnifying-glass" wire:model.live="search" placeholder="Hledat..." />
        </x-slot:middle>
    </x-mary-header>

    <x-mary-card>
        <x-mary-table :headers="$headers" :rows="$machines" with-pagination link="/admin/machines/{kod}/edit">

            @scope('cell_kod', $machine)
                <span class="font-mono text-sm">{{ $machine->kod }}</span>
            @endscope

            @scope('cell_nazev', $machine)
                <span class="font-semibold">{{ $machine->nazev }}</span>
            @endscope

            @scope('cell_pracoviste_nazev', $machine)
                {{ $machine->pracoviste_nazev }}
            @endscope

            @scope('actions', $machine)
                <x-mary-button icon="o-pencil" link="/admin/machines/{{ $machine->kod }}/edit" class="btn-ghost btn-sm" />
            @endscope
        </x-mary-table>
    </x-mary-card>
</div>
