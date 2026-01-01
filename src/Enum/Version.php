<?php

declare(strict_types=1);

namespace App\Enum;

enum Version: string
{
    case PAL = 'PAL';
    case NTSC = 'NTSC';
}
