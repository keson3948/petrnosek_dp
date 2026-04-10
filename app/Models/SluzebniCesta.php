<?php

namespace App\Models;

use App\Models\Traits\HasFirebirdAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SluzebniCesta extends Model
{
    use HasFirebirdAttributes;

    protected $connection = 'firebird';

    protected $table = 'apc_SluzCest';

    protected $primaryKey = 'KlicSluzebniCesty';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'DatumACasOd' => 'datetime',
        'DatumACasDo' => 'datetime',
        'DatumVystaveni' => 'date',
    ];

    public function spolucestujici(): HasMany
    {
        return $this->hasMany(SluzebniCestaSpolucestujici::class, 'KlicSluzebniCesty', 'KlicSluzebniCesty');
    }

    public function doklad(): BelongsTo
    {
        return $this->belongsTo(Doklad::class, 'ZakazkaVyrobniPrikaz', 'SysPrimKlicDokladu');
    }

    public function operace(): BelongsTo
    {
        return $this->belongsTo(Operace::class, 'HlavniCinnost', 'KlicPoloz');
    }

    public function pracovnikSubjekt(): BelongsTo
    {
        return $this->belongsTo(Subjekt::class, 'Pracovnik', 'KlicSubjektu');
    }

    public function zakaznikSubjekt(): BelongsTo
    {
        return $this->belongsTo(Subjekt::class, 'Zakaznik', 'KlicSubjektu');
    }

    public function pracovisteSubjekt(): BelongsTo
    {
        return $this->belongsTo(Subjekt::class, 'MistoRealizacePracoviste', 'KlicSubjektu');
    }

    public function scopeActiveForUser($query, string $klicSubjektu)
    {
        $today = now()->toDateString();

        return $query->where(function ($q) use ($today) {
            $q->whereRaw('CAST("DatumACasOd" AS DATE) <= ?', [$today])
                ->whereRaw('CAST("DatumACasDo" AS DATE) >= ?', [$today]);
        })->where(function ($q) use ($klicSubjektu) {
            $q->where('Pracovnik', $klicSubjektu)
                ->orWhereIn('KlicSluzebniCesty', function ($sub) use ($klicSubjektu) {
                    $sub->select('KlicSluzebniCesty')
                        ->from('apc_SluzCest_SpC')
                        ->where('Spolucestujici', $klicSubjektu);
                });
        });
    }
}
