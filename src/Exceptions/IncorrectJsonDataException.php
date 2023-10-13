<?php

namespace Mitwork\Kalkan\Exceptions;

use InvalidArgumentException;

class IncorrectJsonDataException extends InvalidArgumentException
{
    public static function create(): self
    {
        return new static(__('kalkan::exceptions.incorrect_json_data'));
    }
}
