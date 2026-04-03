<?php

namespace App\Models;

use App\Models\Traits\HasFirebirdAttributes;
use Illuminate\Database\Eloquent\Model;

class KoUdaj extends Model
{
    use HasFirebirdAttributes;

    protected $connection = 'firebird';
    protected $table = 'eca_Subjekty_KoUdaj';
    protected $primaryKey = 'KlicSubjektu';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];

    public function subjekt()
    {
        return $this->belongsTo(Subjekt::class, 'KlicSubjektu', 'KlicSubjektu');
    }

    public function email()
    {
        return $this->where('DruhKontaktnihoUdaje', 'EMAIL')->first();
    }
}
