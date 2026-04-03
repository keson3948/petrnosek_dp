<?php

namespace App\Models;

use App\Models\Traits\HasFirebirdAttributes;
use App\Models\Traits\HasFirebirdGenerator;
use Illuminate\Database\Eloquent\Model;

class EvPodsestav extends Model
{
    use HasFirebirdAttributes, HasFirebirdGenerator;

    protected static string $generator = 'apc_IdEvidencePodsestav';

    protected $connection = 'firebird';
    protected $table = 'acp_EvPodsestav';
    protected $primaryKey = 'ID';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;
    protected $guarded = [];
}
