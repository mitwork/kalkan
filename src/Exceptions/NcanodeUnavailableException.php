<?php

namespace Mitwork\Kalkan\Exceptions;

use InvalidArgumentException;

class NcanodeUnavailableException extends InvalidArgumentException
{
    public static function create(string $error): self
    {
        return new static(__('kalkan::exceptions.ncanode_is_unavailable', ['error' => $error]));
    }
}
