<?php

declare(strict_types=1);

namespace Mitwork\Kalkan\Tests;

use Illuminate\Support\Str;
use Mitwork\Kalkan\Exceptions\KalkanValidationException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Mitwork\Kalkan\Services\KalkanSignatureService::class)]
final class KalkanSignatureServiceTest extends BaseTestCase
{
    public function testXmlSigningIsWorking(): void
    {
        $this->loadCertificates();
        $service = new \Mitwork\Kalkan\Services\KalkanSignatureService();

        $content = Str::random();

        $data = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<test>
  <message>{$content}</message>
</test>
XML;
        $result = $service->signXml($data, $this->certificates[0]['content'], $this->certificates[0]['password']);
        $response = $service->getResponse();

        $this->assertIsArray($response, 'Получен некорректный ответ сервиса');
        $this->assertArrayHasKey('status', $response, 'Ответ не содержит статус');
        $this->assertEquals(200, $response['status'], 'Получен некорректный ответ сервиса');

        $this->assertArrayHasKey('xml', $response, 'Ответ не содержит извлеченные данные');

        $this->assertIsString($result, 'Подписание XML не работает');
    }

    public function testCmsSigningIsWorking(): void
    {
        $this->loadCertificates();
        $service = new \Mitwork\Kalkan\Services\KalkanSignatureService();

        $content = Str::random();

        $result = $service->signCms($content, $this->certificates[0]['content'], $this->certificates[0]['password']);
        $response = $service->getResponse();

        $this->assertIsArray($response, 'Получен некорректный ответ сервиса');
        $this->assertArrayHasKey('status', $response, 'Ответ не содержит статус');
        $this->assertEquals(200, $response['status'], 'Получен некорректный ответ сервиса');

        $this->assertArrayHasKey('cms', $response, 'Ответ не содержит извлеченные данные');

        $this->assertIsString($result, 'Подписание CMS не работает');
    }

    public function testMultipleCmsSigningIsWorking(): void
    {
        $this->loadCertificates();

        $signatureService = new \Mitwork\Kalkan\Services\KalkanSignatureService();
        $validationService = new \Mitwork\Kalkan\Services\KalkanValidationService();

        $content = Str::random();

        $result = $signatureService->signCms($content, $this->certificates[0]['content'], $this->certificates[0]['password']);
        $response = $signatureService->getResponse();

        $this->assertIsArray($response, 'Получен некорректный ответ сервиса');
        $this->assertArrayHasKey('status', $response, 'Ответ не содержит статус');
        $this->assertEquals(200, $response['status'], 'Получен некорректный ответ сервиса');

        $this->assertArrayHasKey('cms', $response, 'Ответ не содержит извлеченные данные');

        $this->assertIsString($result, 'Подписание CMS не работает');

        $result = $signatureService->signCms($content, $this->certificates[0]['content'], $this->certificates[0]['password'], cms: $result);
        $response = $signatureService->getResponse();

        $this->assertIsArray($response, 'Получен некорректный ответ сервиса');
        $this->assertArrayHasKey('status', $response, 'Ответ не содержит статус');
        $this->assertEquals(200, $response['status'], 'Получен некорректный ответ сервиса');

        $this->assertArrayHasKey('cms', $response, 'Ответ не содержит извлеченные данные');

        $this->assertIsString($result, 'Подписание CMS не работает');

        $result = $validationService->verifyCms($result, $content);
        $response = $validationService->getResponse();

        $this->assertTrue($result, 'Проверка подлинности CMS не работает');
        $this->assertCount(2, $response['signers'], 'Сведения о второй подписи не добавлены');

    }

    public function testSingingExceptionsIsWorking(): void
    {
        $this->loadCertificates('revoked');

        $signatureService = new \Mitwork\Kalkan\Services\KalkanSignatureService();
        $validationService = new \Mitwork\Kalkan\Services\KalkanValidationService();

        $content = Str::random();

        foreach ($this->certificates as $certificate) {

            $result = $signatureService->signCms($content, $certificate['content'], $certificate['password']);

            $this->assertThrows(
                fn () => $validationService->verifyCms($result, $content, throw: true),
                KalkanValidationException::class
            );
        }

    }
}
