<?php

namespace Mitwork\Kalkan\Exceptions;

use InvalidArgumentException;
use Mitwork\Kalkan\Traits\TranslatesMessages;

class NcanodeStatusException extends InvalidArgumentException
{
    use TranslatesMessages;

    public static function create(int $status, string $message = ''): self
    {
        return new static(self::message('kalkan::exceptions.incorrect_ncanode_status', ['status' => $status, 'message' => $message]));
    }
}
