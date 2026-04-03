<?php

namespace App\Models;

use App\Models\Traits\HasFirebirdAttributes;
use Illuminate\Database\Eloquent\Model;

class StavPoh extends Model
{
    use HasFirebirdAttributes;

    protected $connection = 'firebird';
    protected $table = 'ecd_StavyPoh';
    protected $primaryKey = null;
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];
}
