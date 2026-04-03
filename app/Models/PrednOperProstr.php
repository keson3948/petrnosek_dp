<?php

namespace App\Models;

use App\Models\Traits\HasFirebirdAttributes;
use App\Models\Traits\HasFirebirdGenerator;
use Illuminate\Database\Eloquent\Model;

class PrednOperProstr extends Model
{
    use HasFirebirdAttributes, HasFirebirdGenerator;

    protected static string $generator = 'apc_IdOperaceKProstredkum';

    protected $connection = 'firebird';
    protected $table = 'apc_PrednOperProstr';

    protected $primaryKey = 'ID';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;
    protected $guarded = [];

    public function operace()
    {
        return $this->belongsTo(Operace::class, 'Operace', 'KlicPoloz');
    }

    public function prostredek()
    {
        return $this->belongsTo(Prostredek::class, 'Prostredek', 'KlicProstredku');
    }

    public function scopeForProstredek($query, $key)
    {
        return $query->where('Prostredek', $key);
    }
}
