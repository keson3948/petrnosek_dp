<?php

namespace App\Models;

use App\Models\Traits\HasFirebirdAttributes;
use App\Models\Traits\HasFirebirdGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionRecord extends Model
{
    use HasFirebirdAttributes, HasFirebirdGenerator;

    protected $connection = 'firebird';

    protected $table = 'apc_ZaznOper';

    protected $primaryKey = 'ID';

    public $incrementing = false;

    protected static string $generator = 'apc_IdZaznamyOperaciJbT';

    const CREATED_AT = 'CTSMP';

    const UPDATED_AT = 'SYSTIMEST';

    protected $fillable = [
        'ID',
        'machine_id',
        'user_id',
        'started_at',
        'ended_at',
        'pracoviste_id',
        'ZakVP_SysPrimKlic',
        'drawing_number',
        'ZakVP_pozice_radku',
        'operation_id',
        'processed_quantity',
        'time_Prevzeti_Pol',
        'time_Dokonceni_Pol',
        'status',
        'notes',
        'ev_podsestav_id',
        'ZakVP_radek_entita',
        'total_paused_min',
        'last_paused_at',
        'CUSR',
        'UPUSR',
        'UPCNT',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'last_paused_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'klic_subjektu');
    }

    public function doklad()
    {
        return $this->belongsTo(Doklad::class, 'ZakVP_SysPrimKlic', 'SysPrimKlicDokladu');
    }

    public function machine()
    {
        return $this->belongsTo(Prostredek::class, 'machine_id', 'KlicProstredku');
    }

    public function operation()
    {
        return $this->belongsTo(Operace::class, 'operation_id', 'KlicPoloz');
    }
}
