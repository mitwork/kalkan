<?php

declare(strict_types=1);

namespace Mitwork\Kalkan\Tests;

use Mitwork\Kalkan\Enums\QrCodeFormat;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Mitwork\Kalkan\Services\QrCodeGenerationService::class)]
final class QrCodeGenerationServiceTest extends BaseTestCase
{
    public function testQrPngGenerationIsWorking(): void
    {
        $service = new \Mitwork\Kalkan\Services\QrCodeGenerationService();

        $result = $service->generate('https://example.com');

        $this->assertIsString($result->getString(), 'Генерация QR-кода в виде base64 вернула некорректный результат');
        $this->assertIsString($result->getDataUri(), 'Генерация QR-кода в виде data uri вернула некорректный результат');
        $this->assertEquals('image/png', $result->getMimeType(), 'Тип изображения не соответствует image/png');
        $this->assertTrue($service->validate('https://example.com'), 'QR-код не содержит корректную ссылку');
        $this->assertFalse($service->validate('https://example.org'), 'QR-код содержит некорректную ссылку');
        $this->assertIsString($service->getError(), 'Отсутствует ошибка после валидации');
    }

    public function testQrSvgGenerationIsWorking(): void
    {
        $service = new \Mitwork\Kalkan\Services\QrCodeGenerationService();

        $result = $service->generate('https://example.com', format: QrCodeFormat::SVG);

        $this->assertIsString($result->getString(), 'Генерация QR-кода в виде base64 вернула некорректный результат');
        $this->assertIsString($result->getDataUri(), 'Генерация QR-кода в виде data uri вернула некорректный результат');
        $this->assertEquals('image/svg+xml', $result->getMimeType(), 'Тип изображения не соответствует image/svg+xml');
    }

    public function testQrWebpGenerationIsWorking(): void
    {
        $service = new \Mitwork\Kalkan\Services\QrCodeGenerationService();

        $result = $service->generate('https://example.com', format: QrCodeFormat::WEBP);

        $this->assertIsString($result->getString(), 'Генерация QR-кода в виде base64 вернула некорректный результат');
        $this->assertIsString($result->getDataUri(), 'Генерация QR-кода в виде data uri вернула некорректный результат');
        $this->assertEquals('image/webp', $result->getMimeType(), 'Тип изображения не соответствует image/webp');
    }
}
