<?php

namespace Mitwork\Kalkan\Exceptions;

use InvalidArgumentException;

class KalkanSignatureException extends InvalidArgumentException
{
    public static function create(string|array $error): self
    {
        if (is_array($error)) {
            $error = print_r($error, true);
        }

        return new static(__('kalkan::exceptions.signature_error', ['error' => $error]));
    }
}
