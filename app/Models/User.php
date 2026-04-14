<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Attendance\Osoba;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Notifications\ResetPasswordNotification;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $connection = 'mysql';

    protected $fillable = [
        'name',
        'email',
        'password',
        'izo',
        'klic_subjektu',
        'manager_id',
        'is_active',
        'color',
        'cislo_mistra',
        'printer_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }


    public function getAttendanceCipAttribute(): ?string
    {
        if (! $this->izo) {
            return null;
        }

        return strtoupper(str_pad(dechex((int) $this->izo), 8, '0', STR_PAD_LEFT));
    }

    public function todayAttendance(): ?array
    {
        try {
            $osoba = $this->attendanceOsoba();
            if ($osoba) {
                $last = $osoba->pruchody()->dnesni()->orderBy('CAS', 'desc')->first();
                if ($last) {
                    return [
                        'time' => $last->cas_time,
                        'type' => (int) $last->DIRECTION === 1 ? 'prichod' : 'odchod',
                    ];
                }
            }
        } catch (\Exception $e) {
        }

        return null;
    }

    public function attendanceOsoba(): ?\App\Models\Attendance\Osoba
    {
        if (! $this->attendance_cip) {
            return null;
        }

        return Osoba::where('CIP', $this->attendance_cip)->first();
    }

    public function printer()
    {
        return $this->belongsTo(Printer::class);
    }

    public function subjekt()
    {
        return $this->belongsTo(Subjekt::class, 'klic_subjektu', 'KlicSubjektu');
    }

    public function productionRecords()
    {
        return $this->hasMany(ProductionRecord::class, 'user_id', 'klic_subjektu');
    }

    public function assignedMachines()
    {
        return $this->hasMany(PrednOsobProstr::class, 'Osoba', 'klic_subjektu');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function subordinates()
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    protected static function booted()
    {
        static::created(function ($user) {
            $user->assignRole('Operator');
        });
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
