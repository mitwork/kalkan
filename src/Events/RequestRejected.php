<?php

namespace Mitwork\Kalkan\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class RequestRejected
{
    use Dispatchable, InteractsWithSockets;

    public int|string $id;

    public string $message;

    public array $result = [];

    public function __construct($id, $message, $result = [])
    {
        $this->id = $id;
        $this->message = $message;
        $this->result = $result;
    }
}
