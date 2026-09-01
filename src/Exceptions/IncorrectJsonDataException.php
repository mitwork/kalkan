<?php

namespace Mitwork\Kalkan\Exceptions;

use InvalidArgumentException;
use Mitwork\Kalkan\Traits\TranslatesMessages;

class IncorrectJsonDataException extends InvalidArgumentException
{
    use TranslatesMessages;

    public static function create(): self
    {
        return new static(self::message('kalkan::exceptions.incorrect_json_data'));
    }
}
