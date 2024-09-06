<?php

declare(strict_types=1);

namespace Nighten\Bc\Contract;

use Nighten\Bc\Dto\Number;
use Nighten\Bc\Dto\Turn;
use Nighten\Bc\State\GameState;

interface CompStrategy
{
    public function getNumber(GameState $state): Number;

    public function answer(GameState $state, int $bulls, int $cows): Turn;
}
