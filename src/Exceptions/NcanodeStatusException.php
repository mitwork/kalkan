<?php

namespace Mitwork\Kalkan\Exceptions;

use InvalidArgumentException;

class NcanodeStatusException extends InvalidArgumentException
{
    public static function create(int $status, string $message = ''): self
    {
        return new static("Wrong HTTP status: `{$status}` with message: `{$message}`");
    }
}
