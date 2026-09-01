<?php

namespace Mitwork\Kalkan\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class RequestRejected
{
    use Dispatchable, InteractsWithSockets;

    public int|string $id;

    public string $message;

    /** @var array<string, mixed> */
    public array $result = [];

    public int|string|null $documentId;

    /**
     * @param  array<string, mixed>  $result
     */
    public function __construct(int|string $id, string $message, array $result = [], int|string|null $documentId = null)
    {
        $this->id = $id;
        $this->message = $message;
        $this->result = $result;
        $this->documentId = $documentId;
    }
}
