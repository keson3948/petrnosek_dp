<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserMachine extends Model
{
    protected $fillable = [
        'user_id',
        'machine_key',
        'machine_name',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function operations(): HasMany
    {
        return MachineOperation::where('machine_key', $this->machine_key)->get();
    }
}
