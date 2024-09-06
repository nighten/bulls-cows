<?php

declare(strict_types=1);

namespace Nighten\Bc\Service;

use Nighten\Bc\Contract\CompStrategy;
use Nighten\Bc\Dto\Number;
use Nighten\Bc\Dto\Turn;
use Nighten\Bc\Exception\AnswerExpectedException;
use Nighten\Bc\Exception\MistakeDetectedException;
use Nighten\Bc\Exception\RequestNumberExpectedException;
use Nighten\Bc\State\GameState;

readonly class Comp implements CompStrategy
{
    public function __construct(
        private NumberChecker $numberChecker,
    ) {
    }

    /**
     * @throws AnswerExpectedException
     * @throws MistakeDetectedException
     */
    public function getNumber(GameState $state): Number
    {
        $list = $state->getList();
        if (count($list) === 0) {
            throw new MistakeDetectedException('User mistake detected. Rerun game by type "new"');
        }
        $k = array_rand($list);
        $number = Number::fromString($list[$k]);
        $state->addCompNumber($number);
        return $number;
    }

    /**
     * @throws RequestNumberExpectedException
     * @throws MistakeDetectedException
     */
    public function answer(GameState $state, int $bulls, int $cows): Turn
    {
        $number = $state->getCompNumber();
        $numberAsString = $number->asString();
        if ($bulls === 4) {
            $state->setList([$number->asString()]);
            return new Turn($number, $bulls, $cows);
        }
        $list = $state->getList();
        foreach ($list as $key => $hiddenNumber) {
            if ($hiddenNumber === $numberAsString) {
                unset($list[$key]);
                continue;
            }
            $check = $this->numberChecker->check(
                Number::fromString($hiddenNumber),
                $number,
            );
            if (!($check->bulls === $bulls && $check->cows === $cows)) {
                unset($list[$key]);
            }
        }
        if (count($list) === 0) {
            throw new MistakeDetectedException('User mistake detected. Rerun game by type "new"');
        }
        $list = array_values($list);
        $state->addCompCheckAnswer($list, $bulls, $cows);
        return new Turn($number, $bulls, $cows);
    }
}
