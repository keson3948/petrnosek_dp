<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Attendance\Pruchod;
use App\Models\SkuZam;
use App\Models\VztahSubj;
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
        'osobni_cislo_dochazky',
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


    public function pruchody()
    {
        return $this->hasMany(Pruchod::class, 'OSC', 'osobni_cislo_dochazky');
    }

    public function todayAttendance(): ?array
    {
        if (! $this->osobni_cislo_dochazky) {
            return null;
        }

        try {
            $last = Pruchod::vceraADnes()
                ->where('OSC', $this->osobni_cislo_dochazky)
                ->orderBy('DATUM', 'desc')
                ->orderBy('CAS', 'desc')
                ->first();

            if ($last) {
                return [
                    'time' => $last->cas_time,
                    'type' => (int) $last->DIRECTION === 1 ? 'prichod' : 'odchod',
                ];
            }
        } catch (\Exception $e) {
        }

        return null;
    }

    public function attendanceStatus(): array
    {
        $pruchody = $this->relationLoaded('pruchody') ? $this->pruchody : collect();

        if ($pruchody->isEmpty()) {
            return ['arrival' => null, 'departure' => null, 'is_present' => false, 'worked_minutes' => 0, 'date' => null];
        }

        $sorted = $pruchody->sortBy([['DATUM', 'asc'], ['CAS', 'asc']])->values();
        $lastArrival = $sorted->last(fn ($p) => (int) $p->DIRECTION === 1);
        $lastDeparture = $sorted->last(fn ($p) => (int) $p->DIRECTION !== 1);

        $departed = $lastDeparture && $lastArrival
            && ($lastDeparture->DATUM . $lastDeparture->CAS) > ($lastArrival->DATUM . $lastArrival->CAS);

        $workedMinutes = $departed
            ? max(0, ((int) $lastDeparture->DATUM - (int) $lastArrival->DATUM) * 1440
                + (int) $lastDeparture->CAS - (int) $lastArrival->CAS)
            : 0;

        return [
            'arrival' => $lastArrival?->cas_time,
            'departure' => $departed ? $lastDeparture->cas_time : null,
            'is_present' => (int) $sorted->last()->DIRECTION === 1,
            'worked_minutes' => $workedMinutes,
            'date' => $lastArrival?->datum_date?->format('d.m.'),
        ];
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

    public function vztah()
    {
        return $this->hasOne(VztahSubj::class, 'Subjekt', 'klic_subjektu')
            ->where('Ukonceno', 0);
    }

    public function activeLunchRecord(): ?ProductionRecord
    {
        $lunch = $this->productionRecords()
            ->lunch()
            ->where('status', 0)
            ->whereDate('started_at', today())
            ->orderByDesc('started_at')
            ->first();

        if (! $lunch) {
            return null;
        }

        $expiresAt = \Carbon\Carbon::parse($lunch->started_at)
            ->addMinutes(ProductionRecord::LUNCH_DURATION_MIN);

        if (now()->greaterThanOrEqualTo($expiresAt)) {
            $lunch->update([
                'status' => 2,
                'ended_at' => $expiresAt,
                'SYSTIMEST' => now(),
            ]);

            return null;
        }

        return $lunch;
    }

    public function hasLunchToday(): bool
    {
        return $this->productionRecords()
            ->lunch()
            ->whereDate('started_at', today())
            ->exists();
    }

    public function currentVztah(): ?VztahSubj
    {
        if (! $this->klic_subjektu) {
            return null;
        }

        if (! $this->relationLoaded('vztah')) {
            $this->load('vztah.skupinaZamestnancu');
        }

        return $this->vztah;
    }

    public function vztahSubj()
    {
        return $this->belongsTo(VztahSubj::class, 'Subjekt', 'klic_subjektu');
    }

    public function employeeGroup(): ?SkuZam
    {
        return $this->currentVztah()?->skupinaZamestnancu;
    }

    public function lunchTime(): ?\Carbon\Carbon
    {
        return $this->employeeGroup()?->lunchCarbon();
    }

    public function canStartLunchNow(): bool
    {
        $window = $this->employeeGroup()?->lunchWindow();

        if (! $window) {
            return false;
        }

        return now()->between($window[0], $window[1]);
    }

    public function assignedMachines()
    {
        return $this->hasMany(PrednOsobProstr::class, 'Osoba', 'klic_subjektu');
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
