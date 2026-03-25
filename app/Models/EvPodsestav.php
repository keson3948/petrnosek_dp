<?php

namespace App\Models;

use App\Models\Traits\HasFirebirdGenerator;
use Illuminate\Database\Eloquent\Model;

class EvPodsestav extends Model
{
    use HasFirebirdGenerator;

    protected static string $generator = 'apc_IdEvidencePodsestav';

    protected $connection = 'firebird';
    protected $table = 'acp_EvPodsestav';
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
}
