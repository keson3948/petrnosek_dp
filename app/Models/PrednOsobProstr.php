<?php

namespace App\Models;

use App\Models\Traits\HasFirebirdAttributes;
use App\Models\Traits\HasFirebirdGenerator;
use Illuminate\Database\Eloquent\Model;

class PrednOsobProstr extends Model
{
    use HasFirebirdAttributes, HasFirebirdGenerator;

    protected static string $generator = 'apc_IdOsobyKProstredkum';

    protected $connection = 'firebird';
    protected $table = 'apc_PrednOsobProstr';

    protected $primaryKey = 'ID';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;
    protected $guarded = [];

    public function prostredek()
    {
        return $this->belongsTo(Prostredek::class, 'Prrostredek', 'KlicProstredku');
    }

    public function scopeForOsoba($query, $key)
    {
        return $query->where('Osoba', $key);
    }
}
