<?php

declare(strict_types=1);

namespace Nighten\Bc\Dto;

readonly class Turn
{
    public function __construct(
        public Number $number,
        public int $bulls,
        public int $cows,
    ) {
    }
}
