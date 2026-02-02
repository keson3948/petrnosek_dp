<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaDokl extends Model
{
    protected $connection = 'firebird';
    protected $table = 'ecd_StaDokl';
    protected $primaryKey = 'Doklad';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];

    public function doklad()
    {
        return $this->belongsTo(Doklad::class, 'Doklad', 'SysPrimKlicDokladu');
    }

    public function scopeTypPohybu($query, $typ)
    {
        return $query->where('TypPohybu', $typ);
    }

    public function scopeVyhodnoceni($query, $val)
    {
        return $query->where('Vyhodnoceni', $val);
    }

    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (is_string($value) && !mb_check_encoding($value, 'UTF-8')) {
            return iconv('WINDOWS-1250', 'UTF-8//IGNORE', $value);
        }

        return $value;
    }
}
