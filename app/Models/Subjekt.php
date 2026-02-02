<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subjekt extends Model
{
    protected $connection = 'firebird';
    protected $table = 'eca_Subjekty';

    protected $primaryKey = 'KlicSubjektu';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];

    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (is_string($value) && !mb_check_encoding($value, 'UTF-8')) {
            return iconv('WINDOWS-1250', 'UTF-8//IGNORE', $value);
        }

        return $value;
    }

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
}
