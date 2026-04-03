<?php

namespace App\Models;

use App\Models\Traits\HasFirebirdAttributes;
use Illuminate\Database\Eloquent\Model;

class Subjekt extends Model
{
    use HasFirebirdAttributes;

    protected static array $trimAttributes = ['KlicSubjektu'];

    protected $connection = 'firebird';

    protected $table = 'eca_Subjekty';

    protected $primaryKey = 'KlicSubjektu';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];

    public function druhSubjektu()
    {
        return $this->belongsTo(DruhSubjektu::class, 'DruhSubjektu', 'KlicDruhuSubjektu');
    }

    public function funkce()
    {
        return $this->belongsTo(FunkceSub::class, 'Funkce', 'KlicFunkce');
    }

    public function scopeTdfDocType($query, $id)
    {
        return $query->where('TdfDocType', $id);
    }

    public function scopeFunkce($query, $id)
    {
        return $query->where('Funkce', $id);
    }

    public function scopeStavSubjektuNot($query, $stav)
    {
        return $query->where('StavSubjektu', '<>', $stav);
    }

    public function koUdaj()
    {
        return $this->hasOne(KoUdaj::class, 'KlicSubjektu', 'KlicSubjektu');
    }

    public function emailKontakt()
    {
        return $this->hasOne(KoUdaj::class, 'KlicSubjektu', 'KlicSubjektu')
            ->where('DruhKontaktnihoUdaje', 'EMAIL')
            ->orderBy('Poradi');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'klic_subjektu', 'KlicSubjektu');
    }

    public function skupinaSubjektu()
    {
        return $this->belongsTo(SkupinaSubjektu::class, 'SkupinSubjektu', 'KlicSkupi');
    }

    public function isMistr(): bool
    {
        return (int) $this->SkupinSubjektu === 1050;
    }
}
