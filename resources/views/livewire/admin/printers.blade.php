<div>
    <x-mary-header title="Správa tiskáren" subtitle="Nastavení štítkovaček a parametrů">
        <x-slot:middle class="!justify-end">
            <x-mary-input icon="o-magnifying-glass" placeholder="Hledat..." />
        </x-slot:middle>
        <x-slot:actions>
            <x-mary-button icon="o-plus" class="btn-primary" @click="$wire.create()" />
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card>
        <x-mary-table :headers="[
            ['key' => 'id', 'label' => '#'],
            ['key' => 'name', 'label' => 'Název'],
            ['key' => 'system_name', 'label' => 'CUPS Název'],
            ['key' => 'ip_address', 'label' => 'IP Adresa'],
            ['key' => 'page_size', 'label' => 'Role'],
            ['key' => 'is_active', 'label' => 'Aktivní'],
        ]" :rows="$printers">

            {{-- Stav --}}
            @scope('cell_is_active', $printer)
            <x-mary-badge :value="$printer->is_active ? 'Ano' : 'Ne'"
                          :class="$printer->is_active ? 'badge-success' : 'badge-ghost'" />
            @endscope

            {{-- Akce --}}
            @scope('actions', $printer)
            <div class="flex gap-2">
                <x-mary-button icon="o-pencil" class="btn-sm btn-ghost" wire:click="edit({{ $printer->id }})" />
                <x-mary-button icon="o-trash" class="btn-sm btn-ghost text-red-500" wire:click="delete({{ $printer->id }})" wire:confirm="Opravdu smazat?" />
            </div>
            @endscope
        </x-mary-table>
    </x-mary-card>

    {{-- MODAL --}}
    <x-mary-modal wire:model="myModal" title="Tiskárna">
        <div class="grid gap-4">
            <x-mary-input label="Název (pro lidi)" wire:model="form.name" placeholder="např. Expedice" />

            <x-mary-input label="Systémový název (CUPS)" wire:model="form.system_name"
                          hint="Musí přesně odpovídat názvu v příkazu lpstat -p" />

            <x-mary-input label="IP Adresa" wire:model="form.ip_address" hint="Pro informaci" />

            <div class="grid grid-cols-2 gap-4">
                <x-mary-select label="Velikost role" :options="$pageSizes" wire:model="form.page_size" />
                <x-mary-select label="Orientace" :options="$orientations" wire:model="form.orientation" />
            </div>

            <x-mary-checkbox label="Aktivní" wire:model="form.is_active" />
        </div>

        <x-slot:actions>
            <x-mary-button label="Zrušit" @click="$wire.myModal = false" />
            <x-mary-button label="Uložit" class="btn-primary" wire:click="save" />
        </x-slot:actions>
    </x-mary-modal>
</div>
