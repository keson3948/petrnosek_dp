<?php

namespace App\Models;

use App\Models\Traits\HasFirebirdGenerator;
use Illuminate\Database\Eloquent\Model;

class PrednOperProstr extends Model
{
    use HasFirebirdGenerator;

    protected static string $generator = 'apc_IdOperaceKProstredkum';

    protected $connection = 'firebird';
    protected $table = 'apc_PrednOperProstr';

    protected $primaryKey = 'ID';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;
    protected $guarded = [];

    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (is_string($value) && !mb_check_encoding($value, 'UTF-8')) {
            return iconv('WINDOWS-1250', 'UTF-8//IGNORE', $value);
        }

        return $value;
    }

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
