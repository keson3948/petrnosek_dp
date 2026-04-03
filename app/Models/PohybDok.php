<?php

namespace App\Models;

use App\Models\Traits\HasFirebirdAttributes;
use Illuminate\Database\Eloquent\Model;

class PohybDok extends Model
{
    use HasFirebirdAttributes;

    protected $connection = 'firebird';
    protected $table = 'ecd_PohybyDok';
    protected $primaryKey = null;
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];
}
