<?php

namespace App\Models;

use App\Models\Traits\HasFirebirdAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SluzebniCestaSpolucestujici extends Model
{
    use HasFirebirdAttributes;

    protected $connection = 'firebird';

    protected $table = 'apc_SluzCest_SpC';

    protected $primaryKey = 'KlicSluzebniCesty';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];

    public function sluzebniCesta(): BelongsTo
    {
        return $this->belongsTo(SluzebniCesta::class, 'KlicSluzebniCesty', 'KlicSluzebniCesty');
    }
}
