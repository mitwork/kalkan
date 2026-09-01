<?php

namespace Mitwork\Kalkan\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class DocumentValidated
{
    use Dispatchable, InteractsWithSockets;

    public int|string $id;

    public string $content;

    public string $signature;

    /** @var array<string, mixed> */
    public array $result;

    public int|string|null $requestId;

    /**
     * @param  array<string, mixed>  $result
     */
    public function __construct(int|string $id, string $content, string $signature, array $result, int|string|null $requestId = null)
    {
        $this->id = $id;
        $this->content = $content;
        $this->signature = $signature;
        $this->result = $result;
        $this->requestId = $requestId;
    }
}
