<div>
    <x-mary-header title="Dashboard" separator>
        <x-slot:middle class="!justify-end">
            <x-mary-button
                label="Začít novou operaci"
                icon="o-play"
                wire:click="openStartDrawer"
                class="btn-primary btn-lg"
                :disabled="$hasActiveRecord"
            />
        </x-slot:middle>
    </x-mary-header>

    <livewire:dashboard.active-record />

    <livewire:dashboard.history />

    <livewire:dashboard.start-drawer
        :qr-start="request()->query('start')"
        :qr-d="request()->query('d')"
    />
</div>
