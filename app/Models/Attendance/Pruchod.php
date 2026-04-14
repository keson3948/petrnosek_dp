<?php

namespace App\Models\Attendance;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pruchod extends Model
{
    protected $connection = 'attendance';

    protected $table = 'pruchod';

    public $timestamps = false;

    protected $guarded = [];

    public function osoba(): BelongsTo
    {
        return $this->belongsTo(Osoba::class, 'OSC', 'OSC');
    }

    protected function datumDate(): Attribute
    {
        return Attribute::get(fn () => Carbon::createFromDate(1900, 1, 1)->addDays($this->DATUM));
    }

    protected function casTime(): Attribute
    {
        return Attribute::get(fn () => sprintf('%02d:%02d', intdiv($this->CAS, 60), $this->CAS % 60));
    }

    protected function isPrichod(): Attribute
    {
        return Attribute::get(fn () => $this->DIRECTION === 1);
    }

    public function scopeDnesni($query)
    {
        return $query->whereRaw("DATUM = DATEDIFF(DAY, '1900-01-01', GETDATE())");
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
