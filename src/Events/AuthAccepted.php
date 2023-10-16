<?php

namespace Mitwork\Kalkan\Events;

use Illuminate\Foundation\Events\Dispatchable;

class AuthAccepted
{
    use Dispatchable;

    public int|string $id;

    public string $token;

    public function __construct($id, $token)
    {
        $this->id = $id;
        $this->token = $token;
    }
}
