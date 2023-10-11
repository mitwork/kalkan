<?php

namespace Mitwork\Kalkan\Exceptions;

use InvalidArgumentException;

class IncorrectXmlException extends InvalidArgumentException
{
    public static function create(): self
    {
        return new static('Wrong XML data');
    }
}
