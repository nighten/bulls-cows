<?php

declare(strict_types=1);

namespace Nighten\Bc\Service;

use Nighten\Bc\Dto\Number;
use Nighten\Bc\Dto\Turn;

class NumberChecker
{
    public function check(Number $hiddenNumber, Number $checkedNumber): Turn
    {
        $bulls = 0;
        $cows = 0;
        $hiddenNumberArray = $hiddenNumber->getNumber();
        $checkedNumberArray = $checkedNumber->getNumber();

        foreach ($checkedNumberArray as $key => $number) {
            if ($hiddenNumberArray[$key] === $number) {
                $bulls++;
            } elseif (in_array($number, $hiddenNumberArray, true)) {
                $cows++;
            }
        }
        return new Turn($checkedNumber, $bulls, $cows);
    }
}
