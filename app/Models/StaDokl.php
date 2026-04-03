<?php

namespace App\Models;

use App\Models\Traits\HasFirebirdAttributes;
use Illuminate\Database\Eloquent\Model;

class StaDokl extends Model
{
    use HasFirebirdAttributes;

    protected $connection = 'firebird';
    protected $table = 'ecd_StaDokl';
    protected $primaryKey = 'Doklad';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];

    public function doklad()
    {
        return $this->belongsTo(Doklad::class, 'Doklad', 'SysPrimKlicDokladu');
    }

    public function scopeTypPohybu($query, $typ)
    {
        return $query->where('TypPohybu', $typ);
    }

    public function scopeVyhodnoceni($query, $val)
    {
        return $query->where('Vyhodnoceni', $val);
    }
}
