<?php

namespace App\Models\Traits;

trait HasFirebirdAttributes
{
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (is_string($value) && !mb_check_encoding($value, 'UTF-8')) {
            return iconv('WINDOWS-1250', 'UTF-8//IGNORE', $value);
        }

        return $value;
    }
}
