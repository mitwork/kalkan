<?php

namespace Mitwork\Kalkan\Exceptions;

use InvalidArgumentException;
use Mitwork\Kalkan\Traits\TranslatesMessages;

class KalkanValidationException extends InvalidArgumentException
{
    use TranslatesMessages;

    /**
     * @param  string|array<string, mixed>  $error
     */
    public static function create(string|array $error): self
    {
        if (is_array($error)) {
            $error = print_r($error, true);
        }

        return new static(self::message('kalkan::exceptions.validation_error', ['error' => $error]));
    }
}
