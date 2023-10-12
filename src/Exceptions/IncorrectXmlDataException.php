<?php

namespace Mitwork\Kalkan\Exceptions;

use InvalidArgumentException;

class IncorrectXmlDataException extends InvalidArgumentException
{
    public static function create(string $error): self
    {
        return new static("Некорректные XML-данные: `{$error}`");
    }
}
