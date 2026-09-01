<?php

namespace Mitwork\Kalkan\Exceptions;

use InvalidArgumentException;
use Mitwork\Kalkan\Traits\TranslatesMessages;

class IncorrectXmlDataException extends InvalidArgumentException
{
    use TranslatesMessages;

    public static function create(string $error): self
    {
        return new static(self::message('kalkan::exceptions.incorrect_xml_data', ['error' => $error]));
    }
}
