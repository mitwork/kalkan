<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Mitwork\Kalkan\Services\QrCodeGenerationService::class)]
final class QrCodeGenerationTest extends TestCase
{
    public function testQrGenerationIsWorking(): void
    {
        $service = new \Mitwork\Kalkan\Services\QrCodeGenerationService();

        $result = $service->generate('https://example.com');

        $this->assertIsString($result->getString(), 'Result string is string');
        $this->assertIsString($result->getDataUri(), 'Result data uri is string');
        $this->assertEquals('image/png', $result->getMimeType(), 'Result content types is not PNG');
        $this->assertTrue($service->validate('https://example.com'), 'QR code dont have actual URL');
        $this->assertFalse($service->validate('https://example.org'), 'QR code have wrong URL');
    }
}
