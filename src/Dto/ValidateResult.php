<?php

declare(strict_types=1);

namespace Nighten\Bc\Dto;

readonly class ValidateResult
{
    public function __construct(
        public bool $success,
        public string $message,
    ) {
    }
}
