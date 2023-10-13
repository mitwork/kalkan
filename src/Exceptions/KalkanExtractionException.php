<?php

namespace Mitwork\Kalkan\Exceptions;

use InvalidArgumentException;

class KalkanExtractionException extends InvalidArgumentException
{
    public static function create(string|array $error): self
    {
        if (is_array($error)) {
            $error = print_r($error, true);
        }

        return new static(__('kalkan::exceptions.extraction_error', ['error' => $error]));
    }
}
