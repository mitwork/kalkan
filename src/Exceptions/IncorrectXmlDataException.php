<?php

namespace Mitwork\Kalkan\Exceptions;

use InvalidArgumentException;

class IncorrectXmlDataException extends InvalidArgumentException
{
    public static function create(string $error): self
    {
        return new static(__('kalkan::exceptions.incorrect_xml_data', ['error' => $error]));
    }
}
