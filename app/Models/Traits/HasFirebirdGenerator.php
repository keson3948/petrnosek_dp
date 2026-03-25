<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\DB;

trait HasFirebirdGenerator
{
    public static function nextId(): int
    {
        $generator = static::$generator;

        return (int) DB::connection('firebird')
            ->selectOne("SELECT NEXT VALUE FOR \"{$generator}\" AS NEXT_ID FROM RDB\$DATABASE")
            ->NEXT_ID;
    }
}
