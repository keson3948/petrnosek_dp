<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VztahSubj extends Model
{
    protected $connection = 'firebird';
    protected $table = 'eca_VztahSubj';

    protected $primaryKey = 'IDVztahSubj';
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

    public function employee()
    {
        return $this->belongsTo(Subjekt::class, 'Subjekt', 'KlicSubjektu');
    }

    public function vedouciSubjekt()
    {
        return $this->belongsTo(Subjekt::class, 'Vedouci', 'KlicSubjektu');
    }

    public function scopeActive($query)
    {
        return $query->where('Ukonceno', 0);
    }
}
