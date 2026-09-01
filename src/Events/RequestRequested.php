<?php

namespace Mitwork\Kalkan\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class RequestRequested
{
    use Dispatchable, InteractsWithSockets;

    public int|string $id;

    /** @var array<string, mixed> */
    public array $request;

    /** @var array<string, mixed> */
    public array $response;

    /**
     * @param  array<string, mixed>  $request
     * @param  array<string, mixed>  $response
     */
    public function __construct(int|string $id, array $request, array $response)
    {
        $this->id = $id;
        $this->request = $request;
        $this->response = $response;
    }
}
