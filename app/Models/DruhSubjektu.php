<?php

namespace App\Models;

use App\Models\Traits\HasFirebirdAttributes;
use Illuminate\Database\Eloquent\Model;

class DruhSubjektu extends Model
{
    use HasFirebirdAttributes;

    protected $connection = 'firebird';
    protected $table = 'eca_DruhSubj';

    protected $primaryKey = 'KlicDruhuSubjektu';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];

    public function subjekty()
    {
        return $this->hasMany(Subjekt::class, 'DruhSubjektu', 'KlicDruhuSubjektu');
    }
}
