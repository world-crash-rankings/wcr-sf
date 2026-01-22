<?php

declare(strict_types=1);

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;
use App\Entity\Score;

class ScoreAdded extends Event
{
    protected Score $score;

    public function __construct(Score $score)
    {
        $this->score = $score;
    }

    public function getScore(): Score
    {
        return $this->score;
    }
}
