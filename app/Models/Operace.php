<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Operace extends Polozka
{
    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope('jen_operace', function (Builder $builder) {
            $builder->where('KlicPoloz', 'like', 'VO%')
                ->where('StavPolozkyNakupuAProdeje', '<>', '939073-XX')
                ->where('Skupina', 'OPERACE');
        });
    }

    /**
     * Get the current quantity (Prijem - Vydej) from StaPo.
     *
     * @return float
     */
    public function getAktualniMnozstviAttribute()
    {
        if ($this->staPo) {
            return (float) ($this->staPo->MnozstviPrijem ?? 0) - (float) ($this->staPo->MnozstviVydej ?? 0);
        }

        return 0.0;
    }
}
