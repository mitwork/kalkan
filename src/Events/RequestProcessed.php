<?php

namespace Mitwork\Kalkan\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class RequestProcessed
{
    use Dispatchable, InteractsWithSockets;

    public int|string $id;

    /** @var array<string, mixed> */
    public array $request;

    /**
     * @param  array<string, mixed>  $request
     */
    public function __construct(int|string $id, array $request)
    {
        $this->id = $id;
        $this->request = $request;
    }
}
