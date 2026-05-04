<x-mail::message>
Dobrý den,

systém automaticky ukončil následující výrobní operace, protože jste odešel/a z pracoviště bez jejich ručního ukončení.

<x-mail::table>
| Projekt / VP | Zahájení | Ukončení |
|:-------------|:--------:|---------:|
@foreach ($closedRecords as $record)
| {{ trim($record->doklad->MPSProjekt ?? '—') }} / {{ trim($record->doklad->KlicDokla ?? '—') }} | {{ \Carbon\Carbon::parse($record->started_at)->format('d.m.Y H:i') }} | {{ \Carbon\Carbon::parse($record->ended_at)->format('H:i') }} |
@endforeach
</x-mail::table>

Příště prosím ukončete operaci před odchodem. Děkujeme.

{{ config('app.name') }}
</x-mail::message>
