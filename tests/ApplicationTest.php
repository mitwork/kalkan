<?php

declare(strict_types=1);

use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Mitwork\Kalkan\Services\DocumentService::class)]
final class ApplicationTest extends TestCase
{
    use WithWorkbench;

    private string $testKey = <<<'CER'
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
CER;

    private string $testPassword = 'Qwerty12';

    public function testApplicationCmsSigning(): void
    {
        $data = [
            'name' => 'hello cms',
            'content' => 'base64',
            'type' => 'cms',
        ];

        // $this->app['config']->set('kalkan.options.ttl', '1');

        $response = $this->post(route('store-document'), $data);

        // dd($response);

        $this->assertTrue($response->isOk());
        $this->assertArrayHasKey('id', $response);

        $id = $response['id'];

        $response = $this->get(route('generate-qr-code', ['id' => $id]));

        $this->assertTrue($response->isOk());

        $this->assertArrayHasKey('image', $response);
        $this->assertArrayHasKey('link', $response);

        $link = $response['link'];

        $response = $this->get($link);

        $this->assertTrue($response->isOk());

        $link = $response['document']['uri'];

        $response = $this->get($link);
        $this->assertTrue($response->isOk());

        // Emulate singing

        $message = (array) $response->original;

        $signatureService = new \Mitwork\Kalkan\Services\KalkanSignatureService();

        foreach ($message['documentsToSign'] as &$document) {
            $document['documentCms'] = $signatureService->signCms($document['documentCms'], $this->testKey, $this->testPassword);
        }

        // Send signed data

        $response = $this->put($link, $message);
        $this->assertTrue($response->isOk());

        $response = $this->get(route('generate-cross-link', ['id' => $id]));

        $this->assertTrue($response->isOk());
    }

    public function testApplicationXmlSigning(): void
    {
        $data = [
            'name' => 'hello xml',
            'content' => '<test>xml</test>',
            'type' => 'xml',
        ];

        $response = $this->post(route('store-document'), $data);

        $this->assertTrue($response->isOk());
        $this->assertArrayHasKey('id', $response);

        $id = $response['id'];

        $response = $this->get(route('generate-qr-code', ['id' => $id]));

        $this->assertTrue($response->isOk());

        $this->assertArrayHasKey('image', $response);
        $this->assertArrayHasKey('link', $response);

        $link = $response['link'];

        $response = $this->get($link);

        $this->assertTrue($response->isOk());

        $link = $response['document']['uri'];

        $response = $this->get($link);

        $this->assertTrue($response->isOk());

        $response = $this->get(route('generate-cross-link', ['id' => $id]));

        $this->assertTrue($response->isOk());
    }
}