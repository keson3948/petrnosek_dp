<div>
    <x-mary-header title="Oblasti" subtitle="Správa oblastí (Areas)" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-mary-input icon="o-magnifying-glass" wire:model.live="search" placeholder="Hledat..." />
        </x-slot:middle>
        <x-slot:actions>
            <x-mary-button icon="o-plus" class="btn-primary" wire:click="create" label="Nová oblast" responsive />
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card>
        <x-mary-table :headers="$headers" :rows="$areas" :sort-by="$sortBy" with-pagination>
            @scope('actions', $area)
                <div class="flex items-center gap-2">
                    <x-mary-button icon="o-pencil" wire:click="edit({{ $area->id }})" class="btn-ghost btn-sm text-blue-500" />
                    <x-mary-button icon="o-trash" wire:click="delete({{ $area->id }})" wire:confirm="Opravdu smazat?" class="btn-ghost btn-sm text-red-500" />
                </div>
            @endscope
        </x-mary-table>
    </x-mary-card>

    <x-mary-drawer wire:model="drawer" title="{{ $area ? 'Úprava oblasti' : 'Nová oblast' }}" right separator with-close-button class="lg:w-1/3">
        <x-mary-form wire:submit="save">
            <x-mary-input label="Název" wire:model="name" />
            <x-mary-input label="Kód" wire:model="code" />
            <x-mary-textarea label="Popis" wire:model="description" rows="5" />

            <x-slot:actions>
                <x-mary-button label="Zrušit" @click="$wire.drawer = false" />
                <x-mary-button label="Uložit" class="btn-primary" type="submit" spinner="save" />
            </x-slot:actions>
        </x-mary-form>
    </x-mary-drawer>
</div>
