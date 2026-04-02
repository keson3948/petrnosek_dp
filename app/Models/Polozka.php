<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Polozka extends Model
{
    protected $connection = 'firebird';

    protected $table = 'ecp_Polozky';

    protected $primaryKey = 'KlicPoloz';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];

    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (is_string($value) && ! mb_check_encoding($value, 'UTF-8')) {
            $value = iconv('WINDOWS-1250', 'UTF-8//IGNORE', $value);
        }

        if (is_string($value) && $key === $this->getKeyName()) {
            return trim($value);
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
