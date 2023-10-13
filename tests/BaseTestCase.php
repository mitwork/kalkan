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
     *
     * @param  string|null  $policy Политика применения
     */
    public function loadCertificates(string $policy = null): void
    {
        $certificates = Yaml::parseFile(__DIR__.'/certificates.yml')['certificates'];

        if ($policy) {
            $certificates = collect($certificates)->where('policy', $policy)->all();
        }

        $this->certificates = $certificates;
    }
}
