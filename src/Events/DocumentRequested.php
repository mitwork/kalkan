<?php

namespace Mitwork\Kalkan\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class DocumentRequested
{
    use Dispatchable, InteractsWithSockets;

    public int|string $id;

    public function __construct($id)
    {
        $this->id = $id;
    }
}
