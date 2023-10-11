<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Mitwork\Kalkan\Services\KalkanSignatureService::class)]
final class SignatureTest extends TestCase
{

    /**
     * @var string
     */
    private string $testKey = <<<DATA
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
DATA;

    /**
     * @var string
     */
    private string $testPassword = 'Qwerty12';


    public function testXmlSigningIsWorking(): void
    {
        $service = new \Mitwork\Kalkan\Services\KalkanSignatureService();

        $data = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<test>
  <message>Hello</message>
</test>
XML;
        $result = $service->signXml($data, $this->testKey, $this->testPassword);

        $this->assertIsString($result, 'XML Signing is not working');
    }

    public function testCmsSigningIsWorking(): void
    {
        $service = new \Mitwork\Kalkan\Services\KalkanSignatureService();

        $data = 'test';

        $result = $service->signCms($data, $this->testKey, $this->testPassword);

        $this->assertIsString($result, 'CMS Signing is not working');
    }
}
