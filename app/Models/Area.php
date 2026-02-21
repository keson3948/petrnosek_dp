<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    protected $fillable = ['name', 'code', 'description'];

    public function terminals(): HasMany
    {
        return $this->hasMany(Terminal::class);
    }
}
