<?php

declare(strict_types=1);

namespace Nighten\Bc\Validator;

use Nighten\Bc\Dto\ValidateResult;

class NumberValidator
{
    public function validate(string $number): ValidateResult
    {
        $result = $this->validateLen($number);
        if (!$result->success) {
            return $result;
        }
        $result = $this->validateNumeric($number);
        if (!$result->success) {
            return $result;
        }
        $result = $this->validateDuplicate($number);
        if (!$result->success) {
            return $result;
        }
        return new ValidateResult(true, '');
    }

    public function validateLen(string $number): ValidateResult
    {
        if (strlen($number) !== 4) {
            return new ValidateResult(false, 'Number must be 4 digits.');
        }
        return new ValidateResult(true, '');
    }

    public function validateNumeric(string $number): ValidateResult
    {
        if (!is_numeric($number)) {
            return new ValidateResult(false, 'Number must be numeric.');
        }
        return new ValidateResult(true, '');
    }

    public function validateDuplicate(string $number): ValidateResult
    {
        $array = str_split($number);
        if (count($array) !== count(array_unique($array))) {
            return new ValidateResult(false, 'Number must not contain duplicates numeric.');
        }
        return new ValidateResult(true, '');
    }
}
