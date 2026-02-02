<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KoUdaj extends Model
{
    protected $connection = 'firebird';
    protected $table = 'eca_Subjekty_KoUdaj';
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

    public function subjekt()
    {
        return $this->belongsTo(Subjekt::class, 'KlicSubjektu', 'KlicSubjektu');
    }

    public function email()
    {
        return $this->where('DruhKontaktnihoUdaje', 'EMAIL')->first();
    }
}
