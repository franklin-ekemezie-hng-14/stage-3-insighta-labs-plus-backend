<?php

namespace App\Enums;

use App\Concerns\EnumHelpers;

enum Country: string
{
    //

    use EnumHelpers;

    case ANGOLA = 'AO';
    case ETHIOPIA = 'ET';
    case GHANA = 'GH';
    case KENYA = 'KE';
    case MADAGASCAR = 'MG';
    case NIGERIA = 'NG';
    case SUDAN = 'SD';
    case TANZANIA = 'TZ';
    case UGANDA = 'UG';
    case UNITED_STATES = 'US';

    // TODO[AI]: Add more country enums, as much as possible

    public function code(): string
    {
        return strtoupper($this->value);
    }

    public function id(): string
    {
        return strtolower($this->value);
    }

    public function name(): string
    {
        return str($this->name)->title()->explode("_")->join(" ");
    }

}
