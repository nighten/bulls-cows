<?php

declare(strict_types=1);

namespace Nighten\Bc;

use Nighten\Bc\Contract\CompStrategy;
use Nighten\Bc\Dto\Number;
use Nighten\Bc\Dto\Turn;
use Nighten\Bc\Exception\GameIsNotRunningException;
use Nighten\Bc\Exception\GameIsRunningException;
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
    public function start(): void
    {
        if ($this->state->isRunning()) {
            throw new GameIsRunningException('GameRunning');
        }
        $this->state->start(
            $this->numberGenerator->generateNumber(),
            $this->sourceListProvider->getSourceList(),
        );

        //$n = $this->comp->getNumber($this->state);
        //$this->comp->answer($this->state, 1, 2);

        //$a = 1;
    }

    /**
     * @throws GameIsRunningException
     */
    public function restart(): void
    {
        $this->state = new GameState();
        $this->start();
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

    public function getCompNumber(): Number
    {
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

    /**
     * @throws TurnNotFoundException
     */
    public function getLastUserTurn(): ?Turn
    {
        $turns = $this->state->getUserTurns();
        if (count($turns) === 0) {
            throw new TurnNotFoundException('Last user turn not found');
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

    public function getTurnCount(): int
    {
        return count($this->getTurns());
    }
}
