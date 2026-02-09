<div>
    @if($polozky->isEmpty())
        <p class="text-gray-500">Žádné položky nebyly nalezeny.</p>
    @else
        @php
            $headers = [
                ['key' => 'KlicPoloz', 'label' => '#'],
                ['key' => 'Nazev1', 'label' => 'Název'],
                ['key' => 'ZaklaJedn', 'label' => 'Základní jednotka'],
                ['key' => 'Material', 'label' => 'Material'],
            ];
        @endphp

        <x-mary-table :headers="$headers" :rows="$polozky" striped/>
    @endif
</div>
