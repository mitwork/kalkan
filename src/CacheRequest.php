<?php

namespace Mitwork\Kalkan;

use Mitwork\Kalkan\Contracts\AbstractRequest;

class CacheRequest implements AbstractRequest
{
    public string $description;

    public array $files;

    public array $auth;

    private array $attributes;

    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;

        $this->auth = $attributes['auth'];
        $this->description = $attributes['description'];
        $this->files = $attributes['files'];
    }

    public function attributes(): array
    {
        return $this->attributes;
    }
}
