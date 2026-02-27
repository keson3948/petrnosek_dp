<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionRecord extends Model
{
    protected $fillable = [
        'user_id',
        'machine_id',
        'order_number',
        'drawing_number',
        'operation_id',
        'processed_quantity',
        'status',
        'started_at',
        'ended_at',
        'total_paused_seconds',
        'last_paused_at',
        'notes',
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
}
