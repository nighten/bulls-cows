<?php

declare(strict_types=1);

namespace Nighten\Bc\State;

use Nighten\Bc\Dto\Number;
use Nighten\Bc\Dto\Turn;
use Nighten\Bc\Exception\GameIsNotRunningException;

class GameState
{
    private bool $isRunning = false;

    private ?Number $number = null;

    /**
     * @var Turn[]
     */
    private array $turns = [];

    public function start(Number $number): void
    {
        $this->isRunning = true;
        $this->number = $number;
    }

    public function isRunning(): bool
    {
        return $this->isRunning;
    }

    public function getNumber(): ?Number
    {
        return $this->number;
    }

    /**
     * @throws GameIsNotRunningException
     */
    public function getNumberStrict(): Number
    {
        if (null === $this->number) {
            throw new GameIsNotRunningException('Game is not running.');
        }
        return $this->number;
    }

    public function addTurn(Turn $turnResult): void
    {
        $this->turns[] = $turnResult;
    }

    /**
     * @return Turn[]
     */
    public function getTurns(): array
    {
        return $this->turns;
    }
}
