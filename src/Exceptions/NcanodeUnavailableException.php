<?php

namespace Mitwork\Kalkan\Exceptions;

use InvalidArgumentException;

class NcanodeUnavailableException extends InvalidArgumentException
{
    public static function create(string $error): self
    {
        return new static("Сервис NCANode недоступен: `{$error}`.");
    }
}
