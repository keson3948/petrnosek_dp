<div>
    {{-- Tlačítko pro otevření modálu --}}
    <x-mary-button label="Tisk" icon="o-printer" @click="$wire.modal = true" />

    {{-- Modál --}}
    <x-mary-modal wire:model="modal" title="Tisk štítku" subtitle="Vyberte tiskárnu a počet kopií">
        <x-mary-form no-separator wire:submit="print">

            {{-- Výběr tiskárny z DB --}}
            <x-mary-select
                label="Tiskárna"
                icon="o-printer"
                :options="$printers"
                option-label="name"
                option-value="id"
                wire:model="selectedPrinterId"
                placeholder="Vyberte tiskárnu..."
            />

            <x-mary-input
                label="Počet kopií"
                wire:model="copies"
                icon="o-hashtag"
                type="number"
                min="1"
            />

            <x-slot:actions>
                <x-mary-button label="Zrušit" @click="$wire.modal = false" />
                <x-mary-button label="Tisk" icon="o-printer" class="btn-primary" type="submit" spinner="print" />
            </x-slot:actions>
        </x-mary-form>
    </x-mary-modal>
</div>
