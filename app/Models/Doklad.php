<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doklad extends Model
{
    protected $connection = 'firebird';
    protected $table = 'ecd_Dokl';

    protected $primaryKey = 'KlicDokla';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];

    public function vlastniOsoba()
    {
        return $this->belongsTo(Subjekt::class, 'VlastniOsoba', 'KlicSubjektu');
    }

    public function rodicZakazka()
    {
        return $this->belongsTo(Doklad::class, 'Zakazka', 'SysPrimKlicDokladu');
    }

    public function radky()
    {
        return $this->hasMany(DoklRadek::class, 'SysPrimKlicDokladu', 'SysPrimKlicDokladu');
    }

    public function scopeTdfDocType($query, $type)
    {
        return $query->where('TdfDocType', $type);
    }

    public function scopeDbcnt($query, $id)
    {
        return $query->where('DBCNTID', $id);
    }

    public function scopeDocYear($query, $year)
    {
        return $query->where('DocYear', '>=', $year);
    }

    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (is_string($value) && !mb_check_encoding($value, 'UTF-8')) {
            return iconv('WINDOWS-1250', 'UTF-8//IGNORE', $value);
        }

        return $value;
    }
}
