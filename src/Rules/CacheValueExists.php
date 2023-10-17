<?php

namespace Mitwork\Kalkan\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Cache;

class CacheValueExists implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! Cache::has($value)) {
            $fail(__('kalkan::messages.cache_not_found'));
        }
    }
}
