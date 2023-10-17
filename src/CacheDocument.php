<?php

namespace Mitwork\Kalkan;

use Mitwork\Kalkan\Contracts\AbstractDocument;

class CacheDocument implements AbstractDocument
{
    public string $name;

    public string $content;

    public string|null $description = null;

    public array $meta;

    public array $auth;

    private array $attributes;

    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;

        $this->name = $attributes['name'];
        $this->content = $attributes['content'];

        if (!empty($attributes['description'])) {
            $this->description = $attributes['description'];
        }

        $this->meta = $attributes['meta'];

        if (! isset($attributes['auth'])) {
            $attributes['auth'] = [];
        }

        $this->auth = $attributes['auth'];
    }

    public function attributes(): array
    {
        return $this->attributes;
    }
}
