<?php

declare(strict_types=1);

namespace Nighten\Bc\Dto;

readonly class Number
{
    /**
     * @var array<int<0, 3>, int<0, 9>>
     */
    private array $number;

    /**
     * @param array<int<0, 3>, int<0, 9>> $number
     */
    public function __construct(array $number)
    {
        $this->number = $number;
    }

    /**
     * @return array<int<0, 3>, int<0, 9>>
     */
    public function getNumber(): array
    {
        return $this->number;
    }

    public function asString(): string
    {
        return implode('', $this->number);
    }

    public static function fromString(string $number): Number
    {
        /** @var array<int<0, 3>, int<0, 9>> $a */
        $a = [
            (int)$number[0],
            (int)$number[1],
            (int)$number[2],
            (int)$number[3],
        ];
        return new self($a);
    }
}
