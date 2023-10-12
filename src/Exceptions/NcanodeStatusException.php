<?php

namespace Mitwork\Kalkan\Exceptions;

use InvalidArgumentException;

class NcanodeStatusException extends InvalidArgumentException
{
    public static function create(int $status, string $message = ''): self
    {
        return new static("Некорректный статус ответ: `{$status}` с сообщением: `{$message}`");
    }
}
