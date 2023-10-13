<?php

namespace Mitwork\Kalkan\Exceptions;

use InvalidArgumentException;

class IncorrectBase64DataException extends InvalidArgumentException
{
    public static function create(): self
    {
        return new static(__('kalkan::exceptions.incorrect_base64_data'));
    }
}
