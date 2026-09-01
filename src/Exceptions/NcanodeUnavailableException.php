<?php

namespace Mitwork\Kalkan\Exceptions;

use InvalidArgumentException;
use Mitwork\Kalkan\Traits\TranslatesMessages;

class NcanodeUnavailableException extends InvalidArgumentException
{
    use TranslatesMessages;

    public static function create(string $error): self
    {
        return new static(self::message('kalkan::exceptions.ncanode_is_unavailable', ['error' => $error]));
    }
}
