<?php

declare(strict_types=1);

namespace App\Enum;

enum GlitchType: string
{
    case NONE = 'None';
    case GLITCH = 'Glitch';
    case SINK = 'Sink';
    case FREEZE = 'Freeze';
}
