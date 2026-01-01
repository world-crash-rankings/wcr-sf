<?php

declare(strict_types=1);

namespace App\Enum;

enum ProofType: string
{
    case PIC = 'Pic';
    case XBL = 'XBL';
    case REPLAY = 'Replay';
    case LIVE = 'Live';
    case FREEZE = 'Freeze';
}
