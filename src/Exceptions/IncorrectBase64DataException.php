<?php

namespace Mitwork\Kalkan\Exceptions;

use InvalidArgumentException;

class IncorrectBase64DataException extends InvalidArgumentException
{
    public static function create(): self
    {
        return new static('Некорректные Base64 данные');
    }
}
