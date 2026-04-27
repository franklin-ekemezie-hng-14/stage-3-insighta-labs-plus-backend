<?php

namespace App\Enums;

use App\Concerns\EnumHelpers;

enum Role: string
{
    //

    use EnumHelpers;


    case ADMIN = 'admin';
    case ANALYST = 'analyst';


    public function abilities(): array
    {
        return match ($this) {
            self::ADMIN     => ['profile:view', 'profile:create', 'profile:update', 'profile:delete'],
            self::ANALYST   => ['profile:view'],
        };
    }

}
