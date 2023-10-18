<?php

declare(strict_types=1);

namespace Mitwork\Kalkan\Tests;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Mitwork\Kalkan\Enums\RequestStatus;
use Mitwork\Kalkan\Events\DocumentRejected;
use Mitwork\Kalkan\Events\RequestProcessed;
use Mitwork\Kalkan\Events\RequestSaved;
use Orchestra\Testbench\Concerns\WithWorkbench;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Mitwork\Kalkan\Events\RequestSaved::class)]
#[CoversClass(\Mitwork\Kalkan\Events\RequestProcessed::class)]
final class EventsTest extends BaseTestCase
{
    use WithWorkbench;

    public function testRequestEvents(): void
    {
        $this->loadCertificates();

        $certificate = $this->certificates[0];

        $content = Str::random(64);

        $data = [
            'name' => 'hello xml',
            'data' => sprintf('<test>%s</test>', $content),
            'mime' => 'text/xml',
            'meta' => [
                'certificate' => $certificate['title'],
            ],
        ];

        Event::fake();

        $response = $this->prepareRequest([$data], $certificate['title']);

        Event::assertDispatched(RequestSaved::class);

        $id = $response['id'];

        $headers = $response['headers'];
        $message = $response['message'];

        $this->signResponse($message, $certificate);

        // Отправка подписанных данных

        Event::fake();

        $response = $this->put($response['link'], $message, $headers);

        Event::assertDispatched(RequestProcessed::class);

        $this->assertTrue($response->isOk(), sprintf('[%s] Ошибка обработки подписанных документов: %s', $certificate['title'], $response->getContent()));

        $this->checkRequestStatus($id, $certificate['title']);
    }

    public function testDocumentEvents(): void
    {
        $this->loadCertificates(['active' => false]);

        $certificates = $this->certificates;

        foreach ($certificates as $certificate) {

            $content = Str::random(64);

            $file = [
                'name' => 'hello expired cms',
                'data' => base64_encode($content),
                'mime' => 'text/plain',
                'meta' => [
                    'certificate' => $certificate['title'],
                ],
            ];

            $response = $this->prepareRequest([$file], $certificate['title']);

            $id = $response['id'];
            $message = $response['message'];

            $signatureService = new \Mitwork\Kalkan\Services\KalkanSignatureService();

            foreach ($message['documentsToSign'] as &$document) {

                $status = $signatureService->signCms($document['document']['file']['data'], $certificate['content'], $certificate['password']);

                if ($status) {
                    $document['document']['file']['data'] = $status;
                }
            }

            // Отправка подписанных данных

            Event::fake();

            $response = $this->put($response['link'], $message);

            Event::assertDispatched(DocumentRejected::class);

            $this->assertFalse($response->isOk(), sprintf('[%s] Некорректный статус для ошибочного документа %s: %s', $id, $certificate['title'], $response->getContent()));

            // Проверка статуса после подписания

            $this->checkRequestStatus($id, $certificate['title'], RequestStatus::REJECTED);
        }
    }
}
