<div>
    <x-mary-header title="Operace" separator>
    </x-mary-header>

    <x-mary-card>
        @php
            $headers = [
                ['key' => 'KlicPoloz', 'label' => 'Klíč', 'class' => 'w-24'],
                ['key' => 'Nazev1', 'label' => 'Název']
            ];
        @endphp

        <x-mary-table :headers="$headers" :rows="$operace" with-pagination>
            @scope('cell_stav_dokladu_nazev', $op)
                {{ $op->stavDokladu->NazevUplny ?? $op->StavPolozkyNakupuAProdeje }}
            @endscope

            @scope('cell_aktualni_mnozstvi', $op)
                @if($op->staPo)
                    {{ number_format($op->aktualni_mnozstvi, $op->staPo->PocetDesetinnychMistZMJ ?? 2, ',', ' ') }} {{ $op->ZaklaJedn }}
                @else
                    {{ number_format(0, 2, ',', ' ') }} {{ $op->ZaklaJedn }}
                @endif
            @endscope

            @scope('cell_sklad_nazev', $op)
                {{ $op->staPo->Sklad ?? '' }}
            @endscope
        </x-mary-table>
    </x-mary-card>
</div>
