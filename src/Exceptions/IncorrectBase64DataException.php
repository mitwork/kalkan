<?php

namespace Mitwork\Kalkan\Exceptions;

use InvalidArgumentException;
use Mitwork\Kalkan\Traits\TranslatesMessages;

class IncorrectBase64DataException extends InvalidArgumentException
{
    use TranslatesMessages;

    public static function create(): self
    {
        return new static(self::message('kalkan::exceptions.incorrect_base64_data'));
    }
}
