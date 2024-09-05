<?php

declare(strict_types=1);

namespace Nighten\Bc\Dto;

readonly class Number
{
    /**
     * @var array{0: int<0,9>, 1: int<0,9>, 2: int<0,9>, 3: int<0,9>}
     */
    private array $number;

    /**
     * @param array{0: int<0,9>, 1: int<0,9>, 2: int<0,9>, 3: int<0,9>} $number
     */
    public function __construct(array $number)
    {
        $this->number = $number;
    }

    /**
     * @return array{0: int<0,9>, 1: int<0,9>, 2: int<0,9>, 3: int<0,9>}
     */
    public function getNumber(): array
    {
        return $this->number;
    }

    public function asString(): string
    {
        return implode('', $this->number);
    }
}
