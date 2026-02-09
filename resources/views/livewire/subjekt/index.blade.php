<div>
    @if($subjekty->isEmpty())
        <p class="text-gray-500">Žádné subjekty nebyly nalezeny.</p>
    @else
        @php
            $headers = [
                ['key' => 'ZobrazovanyNazev', 'label' => 'Zobrazovaný Název'],
                ['key' => 'Ulice2', 'label' => 'Ulice'],
                ['key' => 'Mesto', 'label' => 'Město'],
                ['key' => 'funkce.Nazev', 'label' => 'Funkce'],
                ['key' => 'KlicSubjektu', 'label' => '#'],
            ];
        @endphp

        <x-mary-table :headers="$headers" :rows="$subjekty" striped/>
    @endif

</div>
