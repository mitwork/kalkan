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

    public function __construct($id, $content, $signature)
    {
        $this->id = $id;
        $this->content = $content;
        $this->signature = $signature;
    }
}
