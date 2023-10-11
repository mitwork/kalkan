<?php

namespace Mitwork\Kalkan\Contracts;

interface ExtractionService
{
    public function extractCms(string $cms): string;
}
