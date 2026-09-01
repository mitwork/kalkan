<?php

namespace Mitwork\Kalkan\Contracts;

interface AbstractRequest
{
    /**
     * @return array<string, string>
     */
    public function rules(): array;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
