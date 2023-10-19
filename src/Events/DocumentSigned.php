<?php

namespace Mitwork\Kalkan\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class DocumentSigned
{
    use Dispatchable, InteractsWithSockets;

    public int|string $id;

    public string $content;

    public string $signature;

    public array $result;

    public int|string|null $requestId;

    public function __construct($id, $content, $signature, $result, $requestId = null)
    {
        $this->id = $id;
        $this->content = $content;
        $this->signature = $signature;
        $this->result = $result;
        $this->requestId = $requestId;
    }
}
