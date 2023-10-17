<?php

namespace Mitwork\Kalkan\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class RequestRequested
{
    use Dispatchable, InteractsWithSockets;

    public int|string $id;

    public array $request;

    public array $response;

    public function __construct($id, $request, $response)
    {
        $this->id = $id;
        $this->request = $request;
        $this->response = $response;
    }
}
