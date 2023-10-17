<?php

namespace Mitwork\Kalkan\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class RequestProcessed
{
    use Dispatchable, InteractsWithSockets;

    public int|string $id;

    public array $request;

    public function __construct($id, $request)
    {
        $this->id = $id;
        $this->request = $request;
    }
}
