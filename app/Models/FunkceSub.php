<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FunkceSub extends Model
{
    protected $connection = 'firebird';
    protected $table = 'eca_FunkceSub';
    protected $primaryKey = 'KlicFunkce';
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
}
