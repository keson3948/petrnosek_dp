<?php

namespace App\Models;

use App\Models\Traits\HasFirebirdAttributes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class SkuZam extends Model
{
    use HasFirebirdAttributes;

    public const LUNCH_WINDOW_MIN = 10;

    protected $connection = 'firebird';

    protected $table = 'apc_SkuZam';

    protected $primaryKey = 'KlicSkupinyZamestnancu';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];

    public function lunchCarbon(?Carbon $base = null): ?Carbon
    {
        if ($this->StanovenyCasProObed === null || $this->StanovenyCasProObed === '') {
            return null;
        }

        return ($base ?? now())->copy()->startOfDay()
            ->addSeconds((int) $this->StanovenyCasProObed);
    }

    public function lunchWindow(?Carbon $base = null): ?array
    {
        $lunch = $this->lunchCarbon($base);

        if (! $lunch) {
            return null;
        }

        return [
            $lunch->copy()->subMinutes(self::LUNCH_WINDOW_MIN),
            $lunch->copy()->addMinutes(self::LUNCH_WINDOW_MIN),
        ];
    }
}
