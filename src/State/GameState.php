<?php

declare(strict_types=1);

namespace Nighten\Bc\State;

use Nighten\Bc\Dto\Number;
use Nighten\Bc\Dto\Turn;
use Nighten\Bc\Enum\GameType;
use Nighten\Bc\Exception\AnswerExpectedException;
use Nighten\Bc\Exception\GameIsNotRunningException;
use Nighten\Bc\Exception\RequestNumberExpectedException;

class GameState
{
    public const string PLAYER_USER = 'user';
    public const string PLAYER_COMP = 'comp';

    private bool $isRunning = false;
    private GameType $gameType;

    private ?Number $number = null;

    /**
     * @var Turn[]
     */
    private array $userTurns = [];

    /**
     * @var Turn[]
     */
    private array $compTurns = [];

    private string $player = self::PLAYER_USER;

    /**
     * @var string[]
     */
    private array $list = [];

    private ?Number $compNumber = null;

    /**
     * @param string[] $list
     */
    public function start(
        Number $number,
        GameType $gameType,
        array $list,
    ): void {
        $this->isRunning = true;
        $this->gameType = $gameType;
        $this->number = $number;
        $this->list = $list;
        if ($gameType === GameType::Comp) {
            $this->player = self::PLAYER_COMP;
        } else {
            $this->player = self::PLAYER_USER;
        }
    }

    public function isRunning(): bool
    {
        return $this->isRunning;
    }

    public function getNumber(): ?Number
    {
        return $this->number;
    }

    public function getGameType(): GameType
    {
        return $this->gameType;
    }

    public function isGameTogether(): bool
    {
        return $this->gameType === GameType::Together;
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

    public function addUserTurn(Turn $turnResult): void
    {
        $this->userTurns[] = $turnResult;
        $this->nextTurn();
    }

    /**
     * @return Turn[]
     */
    public function getUserTurns(): array
    {
        return $this->userTurns;
    }

    public function isUserTurn(): bool
    {
        return $this->player === self::PLAYER_USER;
    }

    public function isCompTurn(): bool
    {
        return $this->player === self::PLAYER_COMP;
    }

    /**
     * @return string[]
     */
    public function getList(): array
    {
        return $this->list;
    }

    /**
     * @param string[] $list
     */
    public function setList(array $list): void
    {
        $this->list = $list;
    }

    private function nextTurn(): void
    {
        if ($this->gameType !== GameType::Together) {
            return;
        }
        if ($this->isUserTurn()) {
            $this->player = self::PLAYER_COMP;
        } else {
            $this->player = self::PLAYER_USER;
        }
    }

    public function hasCompNumber(): bool
    {
        return $this->compNumber !== null;
    }

    /**
     * @throws AnswerExpectedException
     */
    public function addCompNumber(Number $number): void
    {
        if (null !== $this->compNumber) {
            throw new AnswerExpectedException('Expected answer');
        }
        $this->compNumber = $number;
    }

    /**
     * @throws RequestNumberExpectedException
     */
    public function getCompNumber(): Number
    {
        if (null === $this->compNumber) {
            throw new RequestNumberExpectedException('Request number before get it');
        }
        return $this->compNumber;
    }

    /**
     * @return Turn[]
     */
    public function getCompTurns(): array
    {
        return $this->compTurns;
    }

    /**
     * @param string[] $list
     * @throws RequestNumberExpectedException
     */
    public function addCompCheckAnswer(array $list, int $bulls, int $cows): void
    {
        if (null === $this->compNumber) {
            throw new RequestNumberExpectedException('Request number before add answer');
        }
        $this->list = $list;
        $this->compTurns[] = new Turn($this->compNumber, $bulls, $cows);
        $this->compNumber = null;
        $this->nextTurn();
    }
}
