<div>
    <x-mary-header title="Terminály" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-mary-input icon="o-magnifying-glass" wire:model.live="search" placeholder="Hledat..." />
        </x-slot:middle>
        <x-slot:actions>
            <x-mary-button icon="o-plus" class="btn-primary" wire:click="create" responsive />
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card>
        <x-mary-table :headers="$headers" :rows="$terminals" :sort-by="$sortBy" with-pagination>
            @scope('cell_is_active', $terminal)
                @if($terminal->is_active)
                    <x-mary-badge value="Aktivní" class="badge-success" />
                @else
                    <x-mary-badge value="Neaktivní" class="badge-error" />
                @endif
            @endscope

            @scope('actions', $terminal)
                <div class="flex items-center gap-2">
                    <x-mary-button icon="o-pencil" wire:click="edit({{ $terminal->id }})" class="btn-ghost btn-sm" />
                    <x-mary-button icon="o-trash" wire:click="delete({{ $terminal->id }})" wire:confirm="Opravdu smazat?" class="btn-ghost btn-sm text-red-500" />
                </div>
            @endscope
        </x-mary-table>
    </x-mary-card>

    <x-mary-modal wire:model="drawer" title="{{ $terminal ? 'Úprava terminálu' : 'Nový terminál' }}" separator with-close-button>
        <x-mary-form wire:submit="save">
            <x-mary-select label="Pracoviště" icon="o-map-pin" :options="$pracoviste" wire:model="klic_pracoviste" placeholder="Vyberte pracoviště..." />

            <x-mary-input label="Název" wire:model="name" />
            <x-mary-input label="Identifikátor (Slug)" wire:model="slug" />

            <x-mary-toggle label="Aktivní" wire:model="is_active" right />

            <x-slot:actions>
                <x-mary-button label="Zrušit" @click="$wire.drawer = false" />
                <x-mary-button label="Uložit" class="btn-primary" type="submit" spinner="save" />
            </x-slot:actions>
        </x-mary-form>
    </x-mary-modal>
</div>
