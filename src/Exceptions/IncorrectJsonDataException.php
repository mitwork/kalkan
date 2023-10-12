<?php

namespace Mitwork\Kalkan\Exceptions;

use InvalidArgumentException;

class IncorrectJsonDataException extends InvalidArgumentException
{
    public static function create(): self
    {
        return new static('Некорректные JSON-данные');
    }
}
