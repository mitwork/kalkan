<?php

namespace Mitwork\Kalkan\Contracts;

interface AbstractDocument
{
    public function rules(): array;

    public function toArray(): array;
}
