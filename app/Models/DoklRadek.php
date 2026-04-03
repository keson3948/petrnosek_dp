<?php

namespace App\Models;

use App\Models\Traits\HasFirebirdAttributes;
use Illuminate\Database\Eloquent\Model;

class DoklRadek extends Model
{
    use HasFirebirdAttributes;

    protected $connection = 'firebird';
    protected $table = 'ecd_Dokl_Rad';
    protected $primaryKey = null;
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];

    public function evPodsestavy()
    {
        return $this->hasMany(EvPodsestav::class, 'EntitaRadkuVP', 'EntitaRad');
    }

    public function materialPolozka()
    {
        return $this->belongsTo(Polozka::class, 'Material', 'KlicPoloz');
    }

    public function povrchoUpPolozka()
    {
        return $this->belongsTo(Polozka::class, 'PovrchoUp', 'KlicPoloz');
    }
}
