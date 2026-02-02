<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Polozka extends Model
{
    protected $connection = 'firebird';  // <- důležité
    protected $table = 'ecp_Polozky';    // jméno tabulky ve Firebirdu

    protected $primaryKey = 'KlicPoloz'; // pokud tvůj PK není 'id'
    public $incrementing = false;        // pokud PK není auto increment
    protected $keyType = 'string';
    public $timestamps = false;          // Firebird většinou nemá created_at/updated_at
    protected $guarded = [];

    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (is_string($value) && !mb_check_encoding($value, 'UTF-8')) {
            return iconv('WINDOWS-1250', 'UTF-8//IGNORE', $value);
        }

        return $value;
    }

    public function stavDokladu()
    {
        return $this->belongsTo(HodnStavuDok::class, 'StavPolozkyNakupuAProdeje', 'ID');
    }

    public function staPo()
    {
        return $this->hasOne(StaPo::class, 'Polozka', 'KlicPoloz');
    }

    public function scopeSkupinaIn($query, $values)
    {
        return $query->whereIn('Skupina', $values);
    }

    public function scopeStavPolozkyNot($query, $value)
    {
        return $query->where('StavPolozkyNakupuAProdeje', '<>', $value);
    }
}
