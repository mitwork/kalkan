<?php

namespace Mitwork\Kalkan\Exceptions;

use InvalidArgumentException;

class NcanodeUnavailableException extends InvalidArgumentException
{
    public static function create(string $error): self
    {
        return new static("NCANode is not available: `{$error}`.");
    }
}
