<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SkupinaSubjektu extends Model
{
    protected $connection = 'firebird';

    protected $table = 'eca_SkuSubj';

    protected $primaryKey = 'KlicSkupi';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];

    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (is_string($value) && ! mb_check_encoding($value, 'UTF-8')) {
            return iconv('WINDOWS-1250', 'UTF-8//IGNORE', $value);
        }

        return $value;
    }

    public function getDisplayNameAttribute(): string
    {
        return trim($this->NazevUplny ?? $this->Zkratka ?? '');
    }
}
