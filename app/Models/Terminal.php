<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Terminal extends Model
{
    protected $fillable = ['klic_pracoviste', 'name', 'slug', 'ip_address', 'is_active', 'last_seen_at'];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function pracoviste(): BelongsTo
    {
        return $this->belongsTo(Pracoviste::class, 'klic_pracoviste', 'KlicPracoviste');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function current(): ?self
    {
        return app()->bound('current_terminal') ? app('current_terminal') : null;
    }

    public static function isTerminal(): bool
    {
        return static::current() !== null;
    }
}
