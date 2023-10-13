<?php

namespace Mitwork\Kalkan\Events;

use Illuminate\Foundation\Events\Dispatchable;

class DocumentSaved
{
    use Dispatchable;

    public int|string $id;

    public function __construct($id)
    {
        $this->id = $id;
    }
}
