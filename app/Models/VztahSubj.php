<?php

namespace App\Models;

use App\Models\Traits\HasFirebirdAttributes;
use Illuminate\Database\Eloquent\Model;

class VztahSubj extends Model
{
    use HasFirebirdAttributes;

    protected $connection = 'firebird';
    protected $table = 'eca_VztahSubj';

    protected $primaryKey = 'IDVztahSubj';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;
    protected $guarded = [];

    public function employee()
    {
        return $this->belongsTo(Subjekt::class, 'Subjekt', 'KlicSubjektu');
    }

    public function vedouciSubjekt()
    {
        return $this->belongsTo(Subjekt::class, 'Vedouci', 'KlicSubjektu');
    }

    public function scopeActive($query)
    {
        return $query->where('Ukonceno', 0);
    }
}
