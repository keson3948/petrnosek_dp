<?php

namespace App\Models;

use App\Models\Traits\HasFirebirdAttributes;
use Illuminate\Database\Eloquent\Model;

class Polozka extends Model
{
    use HasFirebirdAttributes;

    protected $connection = 'firebird';

    protected $table = 'ecp_Polozky';

    protected $primaryKey = 'KlicPoloz';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];

    public function stavDokladu()
    {
        return $this->belongsTo(HodnStavuDok::class, 'StavPolozkyNakupuAProdeje', 'ID');
    }

    public function staPo()
    {
        return $this->hasOne(StaPo::class, 'Polozka', 'KlicPoloz');
    }

    public function scopeSkupinaIn($query, $values)
    {
        return $query->whereIn('Skupina', $values);
    }

    public function scopeStavPolozkyNot($query, $value)
    {
        return $query->where('StavPolozkyNakupuAProdeje', '<>', $value);
    }
}
