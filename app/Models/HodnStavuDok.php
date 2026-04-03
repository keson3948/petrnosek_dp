<?php

namespace App\Models;

use App\Models\Traits\HasFirebirdAttributes;
use Illuminate\Database\Eloquent\Model;

class HodnStavuDok extends Model
{
    use HasFirebirdAttributes;

    protected $connection = 'firebird';
    protected $table = 'wf_HodnStavuDok';
    protected $primaryKey = 'ID';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];
}
