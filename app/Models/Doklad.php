<?php

namespace App\Models;

use App\Models\Traits\HasFirebirdAttributes;
use Illuminate\Database\Eloquent\Model;

class Doklad extends Model
{
    use HasFirebirdAttributes;

    protected static array $trimAttributes = ['KlicDokla', 'SysPrimKlicDokladu'];

    protected $connection = 'firebird';

    protected $table = 'ecd_Dokl';

    protected $primaryKey = 'KlicDokla';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];

    public function vlastniOsoba()
    {
        return $this->belongsTo(Subjekt::class, 'VlastniOsoba', 'KlicSubjektu');
    }

    public function rodicZakazka()
    {
        return $this->belongsTo(Doklad::class, 'Zakazka', 'SysPrimKlicDokladu');
    }

    public function staDoklad()
    {
        return $this->belongsTo(StaDokl::class, 'SysPrimKlicDokladu', 'Doklad');
    }

    public function radky()
    {
        return $this->hasMany(DoklRadek::class, 'SysPrimKlicDokladu', 'SysPrimKlicDokladu');
    }

    public function scopeTdfDocType($query, $type)
    {
        return $query->where('TdfDocType', $type);
    }

    public function scopeDbcnt($query, $id)
    {
        return $query->where('DBCNTID', $id);
    }

    public function scopeDocYear($query, $year)
    {
        return $query->where('DocYear', '>=', $year);
    }

    public const DOKLAD_DBCNT_IDS = [
        10003, 10004, 10005, 10032, 21026, // Obchodní zakázky
        21030,                              // Konstrukce
        10904, 21036,                       // Výrobní příkazy
        21027, 21029,                       // Reklamace
        21023,                              // Režie
    ];

    public function scopeAllTypes($query)
    {
        return $query->whereIn('DBCNTID', self::DOKLAD_DBCNT_IDS)
            ->tdfDocType(410008)
            ->docYear('2022')
            ->where('ZakakaMPSJeUkoncena', 0);
    }

    public function scopeSearchByTerm($query, string $term)
    {
        return $query->where(fn ($q) => $q
            ->whereRaw('CAST("KlicDokla" AS VARCHAR(100)) LIKE ?', ["%{$term}%"])
            ->orWhereRaw('CAST("MPSProjekt" AS VARCHAR(100)) LIKE ?', ["%{$term}%"])
            ->orWhereRaw('CAST("SpecifiSy" AS VARCHAR(100)) LIKE ?', ["%{$term}%"])
        );
    }
}
