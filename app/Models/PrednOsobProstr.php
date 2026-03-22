<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrednOsobProstr extends Model
{
    protected $connection = 'firebird';
    protected $table = 'apc_PrednOsobProstr';

    protected $primaryKey = 'ID';
    public $incrementing = false;
    protected $keyType = 'int';
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

    public function prostredek()
    {
        return $this->belongsTo(Prostredek::class, 'Prrostredek', 'KlicProstredku');
    }

    public function scopeForOsoba($query, $key)
    {
        return $query->where('Osoba', $key);
    }
}
