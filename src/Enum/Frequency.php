<?php

declare(strict_types=1);

namespace App\Enum;

enum Frequency: string
{
    case HZ_50 = '50Hz';
    case HZ_60 = '60Hz';
}
