<?php

declare(strict_types=1);

use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Mitwork\Kalkan\Services\DocumentService::class)]
final class ApplicationTest extends TestCase
{
    use WithWorkbench;

    public function testApplicationCmsSigning(): void
    {
        $data = [
            'name' => 'hello cms',
            'content' => 'hello cms',
            'type' => 'cms',
        ];

        // $this->app['config']->set('kalkan.options.ttl', '1');

        $response = $this->post(route('prepare-document'), $data);

        sleep(2);

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

    public function testApplicationXmlSigning(): void
    {
        $data = [
            'name' => 'hello xml',
            'content' => '<test>xml</test>',
            'type' => 'xml',
        ];

        $response = $this->post(route('prepare-document'), $data);

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
