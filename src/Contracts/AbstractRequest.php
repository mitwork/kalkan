<?php

namespace Mitwork\Kalkan\Contracts;

interface AbstractRequest
{
    public function rules(): array;

    public function toArray(): array;
}
