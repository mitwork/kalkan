<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Mitwork\Kalkan\Services\QrCodeGenerationService::class)]
final class QrCodeGenerationServiceTest extends TestCase
{
    public function testQrGenerationIsWorking(): void
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
}
