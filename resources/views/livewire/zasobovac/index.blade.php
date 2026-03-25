<div>
    <x-mary-header title="Zásobování" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-mary-input icon="o-magnifying-glass" wire:model.live.debounce.300ms="search" placeholder="Hledat doklad, projekt..." clearable />
        </x-slot:middle>
    </x-mary-header>

    <x-mary-card>
        <x-mary-table :headers="$headers" :rows="$staDoklady" striped link="/zasobovac/{doklad_id}">

            @scope('cell_klic_dokla', $item)
                <span class="font-mono text-sm font-semibold">{{ $item->klic_dokla }}</span>
            @endscope

            @scope('cell_specificky_symbol', $item)
                <span class="badge badge-neutral badge-sm font-mono">{{ $item->specificky_symbol }}</span>
            @endscope

            <x-slot:empty>
                <div class="text-center py-10 text-gray-500">
                    <x-mary-icon name="o-inbox" class="w-12 h-12 mx-auto text-gray-300" />
                    <div class="mt-2">Žádné doklady nenalezeny.</div>
                </div>
            </x-slot:empty>

        </x-mary-table>
    </x-mary-card>
</div>
