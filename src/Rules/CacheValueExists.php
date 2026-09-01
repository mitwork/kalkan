<?php

namespace Mitwork\Kalkan\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Cache;
use Mitwork\Kalkan\Traits\TranslatesMessages;

class CacheValueExists implements ValidationRule
{
    use TranslatesMessages;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! Cache::has($value)) {
            $fail(self::message('kalkan::messages.cache_not_found'));
        }
    }
}
