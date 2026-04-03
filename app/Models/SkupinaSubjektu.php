<?php

namespace App\Models;

use App\Models\Traits\HasFirebirdAttributes;
use Illuminate\Database\Eloquent\Model;

class SkupinaSubjektu extends Model
{
    use HasFirebirdAttributes;

    protected $connection = 'firebird';

    protected $table = 'eca_SkuSubj';

    protected $primaryKey = 'KlicSkupi';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];

    public function getDisplayNameAttribute(): string
    {
        return trim($this->NazevUplny ?? $this->Zkratka ?? '');
    }
}
