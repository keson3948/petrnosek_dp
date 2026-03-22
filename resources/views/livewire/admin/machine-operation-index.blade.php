<div>
    <x-mary-header title="Stroje a operace" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-mary-select
                wire:model.live="filterProstredek"
                :options="$prostredkyOptions"
                placeholder="Všechny prostředky..."
                icon="o-funnel"
                class="w-72"
            />
        </x-slot:middle>
        <x-slot:actions>
            <x-mary-button icon="o-plus" class="btn-primary" wire:click="create" responsive />
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card>
        <x-mary-table :headers="$headers" :rows="$records">
            @scope('cell_prostredek_kod', $record)
                <span class="font-mono text-sm">{{ $record->prostredek_kod }}</span>
            @endscope

            @scope('cell_prostredek_nazev', $record)
                <span class="font-semibold">{{ $record->prostredek_nazev }}</span>
            @endscope

            @scope('cell_operace_kod', $record)
                <span class="font-mono text-sm">{{ $record->operace_kod }}</span>
            @endscope

            @scope('cell_operace_nazev', $record)
                {{ $record->operace_nazev }}
            @endscope

            @scope('actions', $record)
                <x-mary-button
                    icon="o-trash"
                    wire:click="delete({{ $record->ID }})"
                    wire:confirm="Opravdu smazat tento vztah?"
                    class="btn-ghost btn-sm text-red-500"
                />
            @endscope
        </x-mary-table>
    </x-mary-card>

    <x-mary-modal wire:model="drawer" title="Přidat vztah" separator with-close-button>
        <x-mary-form wire:submit="save">
            <x-mary-select
                label="Prostředek (stroj)"
                icon="o-wrench-screwdriver"
                :options="$prostredkyOptions"
                wire:model="prostredek"
                placeholder="Vyberte prostředek..."
            />

            <x-mary-select
                label="Operace"
                icon="o-cog"
                :options="$operaceOptions"
                wire:model="operace"
                placeholder="Vyberte operaci..."
            />

            <x-slot:actions>
                <x-mary-button label="Zrušit" @click="$wire.drawer = false" />
                <x-mary-button label="Uložit" class="btn-primary" type="submit" spinner="save" />
            </x-slot:actions>
        </x-mary-form>
    </x-mary-modal>
</div>
