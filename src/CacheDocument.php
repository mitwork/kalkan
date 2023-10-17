<?php

namespace Mitwork\Kalkan;

use Mitwork\Kalkan\Contracts\AbstractDocument;

class CacheDocument implements AbstractDocument
{
    public string $name;

    public string $data;

    public ?string $description = null;

    public array $meta;

    private array $attributes;

    public function __construct(array $attributes)
    {
        $this->name = $attributes['name'];
        $this->data = $attributes['data'];

        if (! empty($attributes['description'])) {
            $this->description = $attributes['description'];
        }

        $this->meta = $attributes['meta'];

        $this->attributes = $attributes;
    }

    public function attributes(): array
    {
        return $this->attributes;
    }
}
