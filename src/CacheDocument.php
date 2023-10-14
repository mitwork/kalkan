<?php

namespace Mitwork\Kalkan;

use Mitwork\Kalkan\Contracts\AbstractDocument;

class CacheDocument implements AbstractDocument
{
    public string $name;

    public string $content;

    public array $meta;

    private array $attributes;

    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;

        $this->name = $attributes['name'];
        $this->content = $attributes['content'];
        $this->meta = $attributes['meta'];
    }

    public function attributes(): array
    {
        return $this->attributes;
    }
}
