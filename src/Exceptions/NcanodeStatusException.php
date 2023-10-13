<?php

namespace Mitwork\Kalkan\Exceptions;

use InvalidArgumentException;

class NcanodeStatusException extends InvalidArgumentException
{
    public static function create(int $status, string $message = ''): self
    {
        return new static(__('kalkan::exceptions.incorrect_ncanode_status', ['status' => $status, 'message' => $message]));
    }
}
