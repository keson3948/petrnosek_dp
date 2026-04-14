<?php

namespace App\Models\Attendance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Osoba extends Model
{
    protected $connection = 'attendance';

    protected $table = 'osoby';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'CIP' => 'string',
    ];

    public function pruchody(): HasMany
    {
        return $this->hasMany(Pruchod::class, 'OSC', 'OSC');
    }

    public function save(array $options = [])
    {
        throw new \RuntimeException('Attendance database is read-only.');
    }

    public function delete()
    {
        throw new \RuntimeException('Attendance database is read-only.');
    }
}
