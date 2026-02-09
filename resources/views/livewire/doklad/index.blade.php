<div>
    <x-mary-card>
        <x-mary-table :headers="$headers" :rows="$staDoklady" striped link="/stadokl/{Doklad}">

            @scope('cell_doklad.vlastniOsoba.Prijmeni', $item)
            {{ $item->doklad->vlastniOsoba->Prijmeni ?? '-' }}
            @endscope

        </x-mary-table>
    </x-mary-card>


</div>
