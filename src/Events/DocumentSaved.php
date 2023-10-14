<?php

namespace Mitwork\Kalkan\Events;

use Illuminate\Foundation\Events\Dispatchable;

class DocumentSaved
{
    use Dispatchable;

    public int|string $id;

    public array $attributes;

    public function __construct($id, $attributes)
    {
        $this->id = $id;
        $this->attributes = $attributes;
    }
}
