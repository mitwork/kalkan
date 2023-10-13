<?php

declare(strict_types=1);

namespace Mitwork\Kalkan\Tests;

use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase;
use Symfony\Component\Yaml\Yaml;

class BaseTestCase extends TestCase
{
    use WithWorkbench;

    protected array $certificates = [];

    /**
     * Загрузка сертификатов
     */
    public function loadCertificates(): void
    {
        $this->certificates = Yaml::parseFile(__DIR__.'/certificates.yml')['certificates'];
    }
}
