<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prostredek extends Model
{
    protected $connection = 'firebird';
    protected $table = 'ecc_Prostredky';
    protected $primaryKey = 'KlicProstredku';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];

    public function scopeDbcnt($query, $id)
    {
        return $query->where('DBCNTID', $id);
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
