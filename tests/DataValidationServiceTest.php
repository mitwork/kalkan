<?php

declare(strict_types=1);

namespace Mitwork\Kalkan\Tests;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Mitwork\Kalkan\Services\DataValidationService::class)]
final class DataValidationServiceTest extends BaseTestCase
{
    public function test_xml_validation(): void
    {
        $service = new \Mitwork\Kalkan\Services\DataValidationService;

        $result = $service->validateXml('');
        $this->assertFalse($result, 'Валидация пустой XML-строки не работает');

        $data = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<test>
  <message>Hello</message>
</test>
XML;

        $result = $service->validateXml($data);
        $this->assertTrue($result, 'Валидация корректной XML-строки не работает');

        $data = '<test><message>Hello</message>';

        $result = $service->validateXml($data);
        $this->assertFalse($result, 'Валидация некорректной XML-строки не работает');
    }

    public function test_json_validation(): void
    {
        $service = new \Mitwork\Kalkan\Services\DataValidationService;

        $result = $service->validateJson('');
        $this->assertFalse($result, 'Валидация пустой JSON-строки не работает');

        $data = <<<'JSON'
{
    "hello": "world"
}
JSON;

        $result = $service->validateJson($data);
        $this->assertTrue($result, 'Валидация корректной JSON-строки не работает');

        $data = '{hello: world, 123}';

        $result = $service->validateJson($data);
        $this->assertFalse($result, 'Валидация некорректной JSON-строки не работает');
    }

    public function test_base64_validation(): void
    {
        $service = new \Mitwork\Kalkan\Services\DataValidationService;

        $result = $service->validateBase64('');
        $this->assertFalse($result, 'Валидация пустой Base64-строки не работает');

        $data = 'dGVzdA=='; // test

        $result = $service->validateBase64($data);
        $this->assertTrue($result, 'Валидация корректной Base64-строки не работает');

        $data = 'base64...';

        $result = $service->validateBase64($data);
        $this->assertFalse($result, 'Валидация некорректной Base64-строки не работает');
    }
}
