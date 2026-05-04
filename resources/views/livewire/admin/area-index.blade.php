<div>
    <x-mary-header title="Pracoviště" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-mary-input icon="o-magnifying-glass" wire:model.live="search" placeholder="Hledat..." />
        </x-slot:middle>
    </x-mary-header>

    <x-mary-card>
        <x-mary-table :headers="$headers" :rows="$areas" />
    </x-mary-card>
</div>
