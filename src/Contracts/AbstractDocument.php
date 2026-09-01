<?php

namespace Mitwork\Kalkan\Contracts;

interface AbstractDocument
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
