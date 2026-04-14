<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Printer extends Model
{
    protected $fillable = [
        'name', 'system_name', 'ip_address', 'port',
        'page_size', 'media_type', 'orientation',
        'is_active', 'is_default'
    ];
}
