<?php

namespace App\Models;

use App\Models\Traits\HasFirebirdAttributes;
use Illuminate\Database\Eloquent\Model;

class Prostredek extends Model
{
    use HasFirebirdAttributes;

    protected $connection = 'firebird';
    protected $table = 'ecc_Prostredky';
    protected $primaryKey = 'KlicProstredku';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];

    public function scopeDbcnt($query, $id)
    {
        return $query->where('DBCNTID', $id);
    }

    public function pracoviste()
    {
        return $this->belongsTo(Pracoviste::class, 'Pracoviste', 'KlicPracoviste');
    }

    public function assignedOperations()
    {
        return $this->hasMany(PrednOperProstr::class, 'Prostredek', 'KlicProstredku');
    }
}
