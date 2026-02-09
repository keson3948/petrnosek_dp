<div>

    <x-mary-card>
        <x-mary-table :headers="$this->headers()" :rows="$polozky" striped>
            @scope('cell_stavDokladu.NazevUplny', $item)
            {{ $item->stavDokladu->NazevUplny ?? '-' }}
            @endscope
        </x-mary-table>
    </x-mary-card>

</div>
