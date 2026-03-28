<div>
    <x-mary-header title="Zásobování" separator progress-indicator>
        <x-slot:actions class="!justify-end">
            <x-mary-select
                wire:model.live="filterMistr"
                :options="$mistrOptions"
                placeholder="Všichni mistři"
                icon="o-user"
                class="w-48"
            />
            <x-mary-input icon="o-magnifying-glass" wire:model.live.debounce.300ms="search"
                          placeholder="Hledat doklad, projekt..." clearable/>
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card>
        <x-mary-table :headers="$headers" :rows="$staDoklady" :sort-by="$sortBy" striped link="/zasobovac/{doklad_id}">

            @scope('cell_klic_dokla', $item)
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded-full shrink-0"
                     style="background-color: {{ $item->mistr_color ?? '#d1d5db' }}"></div>
                <span class="font-mono text-sm font-semibold">{{ $item->klic_dokla }}</span>
            </div>
            @endscope

            @scope('cell_specificky_symbol', $item)
            @if(!empty($item->specificky_symbol))
                <span class="badge badge-neutral badge-sm font-mono">{{ $item->specificky_symbol }}</span>

            @endif
            @endscope

            @scope('cell_termin_datum', $item)
            @if($item->termin_datum && $item->termin_datum !== '-')
                {{ \Carbon\Carbon::parse($item->termin_datum)->format('d.m.Y') }}
            @else
                -
            @endif
            @endscope

            <x-slot:empty>
                <div class="text-center py-10 text-gray-500">
                    <x-mary-icon name="o-inbox" class="w-12 h-12 mx-auto text-gray-300"/>
                    <div class="mt-2">Žádné doklady nenalezeny.</div>
                </div>
            </x-slot:empty>

        </x-mary-table>
    </x-mary-card>
</div>
