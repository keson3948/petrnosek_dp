<div>
    <x-mary-header title="Úprava uživatele: {{ $user->name }}" separator progress-indicator>
        <x-slot:actions>
            <x-mary-button label="Odeslat obnovu hesla" icon="o-envelope" @click="$wire.confirmModal = true" />
        </x-slot:actions>
    </x-mary-header>

    <div class="grid grid-cols-1 lg:grid-cols-1 gap-8">
        <x-mary-card title="Osobní údaje a přístupy">
            <x-mary-form wire:submit="save">

                <x-mary-input label="Jméno" wire:model="name" />
                <x-mary-input label="E-mail" wire:model="email" />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-mary-input label="Čip (IZO)" wire:model="izo" placeholder="Nelze načíst / Prázdné" />
                    <x-mary-input label="Klíč Subjektu" wire:model="klic_subjektu" placeholder="Např. 12345" />
                </div>

                <x-mary-choices
                    label="Role uživatele"
                    wire:model="selectedRoles"
                    :options="$allRoles"
                    option-label="name"
                    option-value="id"
                    no-result-text="Nenalezeno"
                    placeholder="Vyberte role..."
                />

                <x-slot:actions>
                    <x-mary-button label="Uložit změny" class="btn-primary" type="submit" spinner="save" />
                </x-slot:actions>
            </x-mary-form>
        </x-mary-card>
    </div>

    <x-mary-modal wire:model="confirmModal" class="backdrop-blur">
        Opravdu chcete odeslat odkaz pro obnovení hesla na e-mail {{ $user->email }}?

        <x-slot:actions>
            <x-mary-button label="Zrušit" @click="$wire.confirmModal = false" />
            <x-mary-button label="Odeslat" class="btn-primary" wire:click="sendResetLink({{ $user->id }})" />
        </x-slot:actions>
    </x-mary-modal>
</div>
