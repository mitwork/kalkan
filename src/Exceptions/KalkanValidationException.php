<?php

namespace Mitwork\Kalkan\Exceptions;

use InvalidArgumentException;

class KalkanValidationException extends InvalidArgumentException
{
    public static function create(string|array $error): self
    {
        if (is_array($error)) {
            $error = print_r($error, true);
        }

        return new static(__('kalkan::exceptions.validation_error', ['error' => $error]));
    }
}
