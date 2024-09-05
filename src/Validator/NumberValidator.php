<?php

declare(strict_types=1);

namespace Nighten\Bc\Validator;

use Nighten\Bc\Dto\ValidateResult;

class NumberValidator
{
    public function validate(string $number): ValidateResult
    {
        if (strlen($number) !== 4) {
            return new ValidateResult(false, 'Number must be 4 digits.');
        }
        if (!is_numeric($number)) {
            return new ValidateResult(false, 'Number must be numeric.');
        }
        $array = str_split($number);
        if (count($array) !== count(array_unique($array))) {
            return new ValidateResult(false, 'Number must not contain duplicates numeric.');
        }
        return new ValidateResult(true, '');
    }
}
