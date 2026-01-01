<?php

declare(strict_types=1);

namespace App\Enum;

enum Platform: string
{
    case GC = 'GC';
    case XBOX = 'Xbox';
    case PS2 = 'PS2';
}
