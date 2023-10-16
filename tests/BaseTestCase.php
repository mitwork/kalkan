<?php

declare(strict_types=1);

namespace Mitwork\Kalkan\Tests;

use Illuminate\Support\Collection;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase;
use Symfony\Component\Yaml\Yaml;

class BaseTestCase extends TestCase
{
    use WithWorkbench;

    protected Collection $certificates;

    /**
     * Загрузка сертификатов
     *
     * @param  array  $filters Фильтры
     */
    public function loadCertificates(array $filters = ['active' => true, 'policy' => 'sign']): void
    {
        $certificates = Yaml::parseFile(__DIR__.'/certificates.yml')['certificates'];

        $this->certificates = collect($certificates)->filter(function ($item) use ($filters) {
            foreach ($filters as $key => $value) {
                if ($item[$key] !== $value) {
                    return false;
                }
            }

            return true;
        });
    }
}
