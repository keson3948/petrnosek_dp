<?php

namespace App\Models;

use App\Models\Traits\HasFirebirdAttributes;
use Illuminate\Database\Eloquent\Model;

class Pracoviste extends Model
{
    use HasFirebirdAttributes;

    protected $connection = 'firebird';
    protected $table = 'ecv_Pracoviste';

    protected $primaryKey = 'KlicPracoviste';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];

    public function vedouci()
    {
        return $this->belongsTo(Subjekt::class, 'VedouciOsoba', 'KlicSubjektu');
    }
}
