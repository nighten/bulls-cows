<?php

declare(strict_types=1);

namespace Nighten\Bc;

use Nighten\Bc\Contract\CompStrategy;
use Nighten\Bc\Dto\Number;
use Nighten\Bc\Dto\Turn;
use Nighten\Bc\Enum\GameType;
use Nighten\Bc\Exception\GameIsNotRunningException;
use Nighten\Bc\Exception\GameIsRunningException;
use Nighten\Bc\Exception\RequestNumberExpectedException;
use Nighten\Bc\Exception\TurnNotFoundException;
use Nighten\Bc\Exception\WrongNumberException;
use Nighten\Bc\Exception\WrongTurnException;
use Nighten\Bc\Service\NumberChecker;
use Nighten\Bc\Service\NumberGenerator;
use Nighten\Bc\Service\SourceListProvider;
use Nighten\Bc\State\GameState;
use Nighten\Bc\Validator\NumberValidator;

class Game
{
    private GameState $state;

    public function __construct(
        private readonly NumberValidator $numberValidator,
        private readonly NumberGenerator $numberGenerator,
        private readonly NumberChecker $numberChecker,
        private readonly SourceListProvider $sourceListProvider,
        private readonly CompStrategy $comp,
    ) {
        $this->state = new GameState();
    }

    /**
     * @throws GameIsRunningException
     */
    public function start(GameType $gameType): void
    {
        if ($this->state->isRunning()) {
            throw new GameIsRunningException('GameRunning');
        }
        $this->state->start(
            $this->numberGenerator->generateNumber(),
            $gameType,
            $this->sourceListProvider->getSourceList(),
        );
    }

    /**
     * @throws GameIsRunningException
     */
    public function restart(): void
    {
        $gameType = $this->state->getGameType();
        $this->state = new GameState();
        $this->start($gameType);
    }

    /**
     * @throws GameIsNotRunningException
     * @throws WrongNumberException
     * @throws WrongTurnException
     */
    public function userTurn(string $turnNumber): Turn
    {
        if (!$this->state->isRunning()) {
            throw new GameIsNotRunningException('GameNotRunning');
        }
        if (!$this->state->isUserTurn()) {
            throw new WrongTurnException('Now in not user turn');
        }
        $result = $this->numberValidator->validate($turnNumber);
        if (!$result->success) {
            throw new WrongNumberException($result->message);
        }
        $array = str_split($turnNumber);
        /**
         * Guarantee by NumberValidator::validate()
         * @var array<int<0, 3>, int<0, 9>> $array
         */
        $array = array_map('intval', $array);
        $turn = $this->numberChecker->check(
            $this->state->getNumberStrict(),
            new Number($array),
        );
        $this->state->addUserTurn($turn);
        return $turn;
    }

    /**
     * @throws RequestNumberExpectedException
     */
    public function getCompNumber(): Number
    {
        if ($this->state->hasCompNumber()) {
            return $this->state->getCompNumber();
        }
        return $this->comp->getNumber($this->state);
    }

    public function getCompNumberAnswer(int $bulls, int $cows): Turn
    {
        return $this->comp->answer($this->state, $bulls, $cows);
    }

    /**
     * @return Turn[]
     */
    public function getUserTurns(): array
    {
        return $this->state->getUserTurns();
    }

    /**
     * @return array{"user": Turn|null, "comp": Turn|null}[]
     */
    public function getTurns(): array
    {
        $result = [];
        $user = $this->state->getUserTurns();
        $comp = $this->state->getCompTurns();
        foreach ($user as $key => $turn) {
            if (!array_key_exists($key, $result)) {
                $result[$key] = ['user' => $turn, 'comp' => null];
            } else {
                $result[$key]['user'] = $turn;
            }
        }
        foreach ($comp as $key => $turn) {
            if (!array_key_exists($key, $result)) {
                $result[$key] = ['user' => null, 'comp' => $turn];
            } else {
                $result[$key]['comp'] = $turn;
            }
        }
        return $result;
    }

    public function getLastUserTurn(): ?Turn
    {
        $turns = $this->state->getUserTurns();
        if (count($turns) === 0) {
            return null;
        }
        return $turns[array_key_last($turns)];
    }

    public function getLastCompTurn(): ?Turn
    {
        $turns = $this->state->getCompTurns();
        if (count($turns) === 0) {
            return null;
        }
        return $turns[array_key_last($turns)];
    }

    public function getState(): GameState
    {
        return $this->state;
    }

    public function loadState(GameState $state): void
    {
        $this->state = $state;
    }

    public function isUserTurn(): bool
    {
        return $this->state->isUserTurn();
    }

    public function isCompTurn(): bool
    {
        return $this->state->isCompTurn();
    }

    public function isTurnFinished(): bool
    {
        //Возможно лучше перенести в state
        if ($this->state->isGameTogether()) {
            $turns = $this->getTurns();
            if (count($turns) === 0) {
                return true;
            }
            $turn = $turns[array_key_last($turns)];
            if ($turn['comp'] === null || $turn['user'] === null) {
                return false;
            }
        }
        return true;
    }

    public function getTurnCount(): int
    {
        return count($this->getTurns());
    }
}
