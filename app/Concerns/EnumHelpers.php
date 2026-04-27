<?php

namespace App\Concerns;

trait EnumHelpers
{
    //

    public static function values(): array
    {
        return collect(self::cases())
            ->map(fn (self $gender) => $gender->value)
            ->toArray();
    }
}
