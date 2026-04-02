<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionRecord extends Model
{
    protected $fillable = [
        'user_id',
        'machine_id',
        'SysPrimKlicDokladu',
        'drawing_number',
        'operation_id',
        'processed_quantity',
        'status',
        'started_at',
        'ended_at',
        'total_paused_seconds',
        'last_paused_at',
        'worked_minutes',
        'notes',
        'ev_podsestav_id',
        'doklad_radek_entita',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'last_paused_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Vztah na Doklad (Firebird) přes SysPrimKlicDokladu.
     * Pozn.: cross-database vztah — eager loading funguje, ale join ne.
     */
    public function doklad()
    {
        return $this->belongsTo(Doklad::class, 'SysPrimKlicDokladu', 'SysPrimKlicDokladu');
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
