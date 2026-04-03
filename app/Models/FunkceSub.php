<?php

namespace App\Models;

use App\Models\Traits\HasFirebirdAttributes;
use Illuminate\Database\Eloquent\Model;

class FunkceSub extends Model
{
    use HasFirebirdAttributes;

    protected $connection = 'firebird';
    protected $table = 'eca_FunkceSub';
    protected $primaryKey = 'KlicFunkce';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];
}
