<?php

namespace Mitwork\Kalkan\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class AuthRejected
{
    use Dispatchable, InteractsWithSockets;

    public int|string $id;

    public string $message;

    public function __construct($id, $message)
    {
        $this->id = $id;
        $this->message = $message;
    }
}
