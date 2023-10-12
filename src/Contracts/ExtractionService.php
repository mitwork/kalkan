<?php

namespace Mitwork\Kalkan\Contracts;

interface ExtractionService
{
    /**
     * Extract CMS data
     *
     * @param  string  $cms CMS-данные
     * @return string Исходные данные
     */
    public function extractCms(string $cms): string;
}
