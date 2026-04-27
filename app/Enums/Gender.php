<?php

namespace App\Enums;

use App\Concerns\EnumHelpers;

enum Gender: string
{
    //

    use EnumHelpers;

    case MALE = 'male';
    case FEMALE = 'female';

}
