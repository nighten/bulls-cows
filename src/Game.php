<?php

declare(strict_types=1);

namespace Nighten\Bc;

use Nighten\Bc\Dto\Number;
use Nighten\Bc\Dto\Turn;
use Nighten\Bc\Exception\GameIsNotRunningException;
use Nighten\Bc\Exception\GameIsRunningException;
use Nighten\Bc\Exception\WrongNumberException;
use Nighten\Bc\Service\NumberChecker;
use Nighten\Bc\Service\NumberGenerator;
use Nighten\Bc\State\GameState;
use Nighten\Bc\Validator\NumberValidator;

class Game
{
    private GameState $state;

    public function __construct(
        private readonly NumberValidator $numberValidator,
        private readonly NumberGenerator $numberGenerator,
        private readonly NumberChecker $numberChecker,
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
        );
    }

    public function restart(): void
    {
        $this->state = new GameState();
        $this->state->start(
            $this->numberGenerator->generateNumber(),
        );
    }

    /**
     * @throws GameIsNotRunningException
     * @throws WrongNumberException
     */
    public function turn(string $turnNumber): Turn
    {
        if (!$this->state->isRunning()) {
            throw new GameIsNotRunningException('GameNotRunning');
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
        $this->state->addTurn($turn);
        return $turn;
    }

    /**
     * @return Turn[]
     */
    public function getTurns(): array
    {
        return $this->state->getTurns();
    }

    public function getState(): GameState
    {
        return $this->state;
    }

    public function loadState(GameState $state): void
    {
        $this->state = $state;
    }
}
