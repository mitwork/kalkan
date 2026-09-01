<?php

namespace Mitwork\Kalkan\Events;

use Illuminate\Foundation\Events\Dispatchable;

class DocumentSaved
{
    use Dispatchable;

    public int|string $id;

    /** @var array<string, mixed> */
    public array $attributes;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(int|string $id, array $attributes)
    {
        $this->id = $id;
        $this->attributes = $attributes;
    }
}
