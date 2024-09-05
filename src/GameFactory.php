<?php

declare(strict_types=1);

namespace Nighten\Bc;

use Nighten\Bc\Service\NumberChecker;
use Nighten\Bc\Service\NumberGenerator;
use Nighten\Bc\Validator\NumberValidator;

class GameFactory
{
    public static function create(): Game
    {
        return new Game(
            new NumberValidator(),
            new NumberGenerator(),
            new NumberChecker(),
        );
    }
}
