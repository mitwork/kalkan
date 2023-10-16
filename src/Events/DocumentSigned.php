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

    public function __construct($id, $content, $signature, $result)
    {
        $this->id = $id;
        $this->content = $content;
        $this->signature = $signature;
        $this->result = $result;
    }
}
