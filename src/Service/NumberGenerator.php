<?php

declare(strict_types=1);

namespace Nighten\Bc\Service;

use Nighten\Bc\Dto\Number;

readonly class NumberGenerator
{
    public function generateNumber(): Number
    {
        $list = [];
        for ($i = 0; $i < 4; $i++) {
            $n = rand(0, 9);
            while (in_array($n, $list)) {
                $n = rand(0, 9);
            }
            $list[$i] = $n;
        }
        return new Number($list);
    }
}
