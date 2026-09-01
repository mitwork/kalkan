<?php

namespace Mitwork\Kalkan\Contracts;

interface ExtractionService
{
    /**
     * Extract CMS data
     *
     * @param  string  $cms  CMS-данные
     * @return string Исходные данные
     *
     * @throws \JsonException
     */
    public function extractCms(string $cms): string;
}
