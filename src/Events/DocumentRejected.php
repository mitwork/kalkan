<?php

namespace Mitwork\Kalkan\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class DocumentRejected
{
    use Dispatchable, InteractsWithSockets;

    public int|string $id;

    public string $message;

    /** @var array<string, mixed> */
    public array $result = [];

    public int|string|null $requestId;

    /**
     * @param  array<string, mixed>  $result
     */
    public function __construct(int|string $id, string $message, array $result = [], int|string|null $requestId = null)
    {
        $this->id = $id;
        $this->message = $message;
        $this->result = $result;
        $this->requestId = $requestId;
    }
}
