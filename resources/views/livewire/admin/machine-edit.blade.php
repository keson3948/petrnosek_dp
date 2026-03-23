<div>
    <x-mary-header title="Stroj: {{ trim($machine->KlicProstredku) }} — {{ trim($machine->NazevUplny ?? '') }}" separator progress-indicator>
        <x-slot:actions>
            <x-mary-button label="Zpět na seznam" icon="o-arrow-left" link="{{ route('admin.machines') }}" />
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card title="Pracoviště">
        <x-mary-form wire:submit="savePracoviste">
            <x-mary-select
                label="Pracoviště"
                icon="o-building-office"
                :options="$pracovisteOptions"
                wire:model="pracoviste"
                placeholder="Vyberte pracoviště..."
            />

            <x-slot:actions>
                <x-mary-button label="Uložit pracoviště" class="btn-primary" type="submit" spinner="savePracoviste" />
            </x-slot:actions>
        </x-mary-form>
    </x-mary-card>

    <x-mary-card title="Přiřazené operace" class="mt-8">
        <x-slot:menu>
            <x-mary-button icon="o-plus" class="btn-primary btn-sm" wire:click="createOperation" />
        </x-slot:menu>

        @if($operations->isEmpty())
            <div class="text-gray-500 text-sm py-4">Žádné operace nejsou přiřazeny.</div>
        @else
            <div
                x-data
                x-init="Sortable.create($el, {
                    handle: '.sortable-handle',
                    animation: 150,
                    onEnd() {
                        const ids = [...$el.children].map(c => parseInt(c.dataset.sortableId));
                        $wire.reorderOperations(ids);
                    }
                })"
                class="space-y-2"
            >
                @foreach($operations as $operation)
                    <div data-sortable-id="{{ $operation->ID }}"
                         class="flex items-center gap-3 p-3 bg-base-100 border border-base-300 rounded-lg">
                        <div class="sortable-handle cursor-grab active:cursor-grabbing text-gray-400 hover:text-gray-600">
                            <x-mary-icon name="o-bars-3" class="w-5 h-5" />
                        </div>
                        <span class="font-mono text-sm w-24 shrink-0">{{ $operation->operace_kod }}</span>
                        <span class="font-semibold flex-1">{{ $operation->operace_nazev }}</span>
                        <x-mary-button
                            icon="o-trash"
                            wire:click="removeOperation({{ $operation->ID }})"
                            wire:confirm="Opravdu odebrat tuto operaci?"
                            class="btn-ghost btn-sm text-red-500"
                        />
                    </div>
                @endforeach
            </div>
        @endif
    </x-mary-card>

    <x-mary-modal wire:model="operationModal" title="Přidat operaci" separator with-close-button>
        <x-mary-form wire:submit="saveOperation">
            <x-mary-select
                label="Operace"
                icon="o-cog"
                :options="$operaceOptions"
                wire:model="operationKey"
                placeholder="Vyberte operaci..."
            />

            <x-slot:actions>
                <x-mary-button label="Zrušit" @click="$wire.operationModal = false" />
                <x-mary-button label="Přiřadit" class="btn-primary" type="submit" spinner="saveOperation" />
            </x-slot:actions>
        </x-mary-form>
    </x-mary-modal>
</div>
