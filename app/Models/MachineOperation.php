<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MachineOperation extends Model
{
    protected $fillable = [
        'machine_key',
        'operation_key',
        'operation_name',
    ];
}
