<?php

namespace Mitwork\Kalkan\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class DocumentRejected
{
    use Dispatchable, InteractsWithSockets;

    public int|string $id;

    public string $message;

    public array $result = [];

    public int|string|null $requestId;

    public function __construct($id, $message, $result = [], $requestId = null)
    {
        $this->id = $id;
        $this->message = $message;
        $this->result = $result;
        $this->requestId = $requestId;
    }
}
