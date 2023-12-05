<?php

declare(strict_types=1);

namespace Mitwork\Kalkan\Tests;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Mitwork\Kalkan\Enums\AuthType;
use Mitwork\Kalkan\Enums\DocumentStatus;
use Mitwork\Kalkan\Enums\RequestStatus;
use Mitwork\Kalkan\Events\AuthAccepted;
use Mitwork\Kalkan\Events\AuthRejected;
use Mitwork\Kalkan\Events\DocumentSigned;
use Orchestra\Testbench\Concerns\WithWorkbench;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Mitwork\Kalkan\Services\IntegrationService::class)]
final class ActionsTest extends BaseTestCase
{
    use WithWorkbench;

    public function testSingleCmsSigning(): void
    {
        $this->loadCertificates();

        $certificates = [$this->certificates[0]];

        foreach ($certificates as $certificate) {

            $content = Str::random(64);

            $data = [
                'name' => 'hello cms',
                'data' => base64_encode($content),
                'mime' => 'text/plain',
                'meta' => [
                    'certificate' => $certificate['title'],
                ],
            ];

            $response = $this->prepareRequest([$data], $certificate['title']);

            $id = $response['id'];

            $headers = $response['headers'];
            $message = $response['message'];

            $signatureService = new \Mitwork\Kalkan\Services\KalkanSignatureService();

            $ids = [];

            foreach ($message['documentsToSign'] as &$document) {

                $status = $signatureService->signCms($document['document']['file']['data'], $certificate['content'], $certificate['password']);

                if ($status) {
                    $document['document']['file']['data'] = $status;
                }

                $ids[] = $document['id'];
            }

            // Отправка подписанных данных

            $response = $this->put($response['link'], $message, $headers);

            $this->assertTrue($response->isOk(), sprintf('[%s] Ошибка обработки подписанных документов: %s', $certificate['title'], $response->getContent()));

            // Проверка статуса заявки после подписания

            $response = $this->get(route(config('kalkan.actions.check-request'), $id));

            $this->assertTrue($response->isOk(), sprintf('[%s] Ошибка получения статуса запроса %s: %s', $id, $certificate['title'], $response->getContent()));
            $this->assertArrayHasKey('status', $response, sprintf('[%s] Ответ не содержит статус заявки %s: %s', $id, $certificate['title'], $response->getContent()));
            $this->assertTrue($response['status'] === RequestStatus::PROCESSED->value, sprintf('[%s] Некорректный статус для обработанной заявки %s: %s', $id, $certificate['title'], $response->getContent()));

            // Проверка статуса документов после подписания

            foreach ($ids as $id) {

                $response = $this->get(route(config('kalkan.actions.check-document'), $id));

                $this->assertTrue($response->isOk(), sprintf('[%s] Ошибка получения статуса документа %s: %s', $id, $certificate['title'], $response->getContent()));
                $this->assertArrayHasKey('status', $response, sprintf('[%s] Ответ не содержит статус документа %s: %s', $id, $certificate['title'], $response->getContent()));
                $this->assertTrue($response['status'] === DocumentStatus::SIGNED->value, sprintf('[%s] Некорректный статус для подписанного документа %s: %s', $id, $certificate['title'], $response->getContent()));
            }
        }
    }

    public function testMultiCmsSigning(): void
    {
        $this->loadCertificates();
        $prefix = 'CMS Multi';

        $content = Str::random(64);
        $certificates = [$this->certificates[0], $this->certificates[2]];

        $files = [
            [
                'name' => 'hello multi cms 1',
                'data' => base64_encode($content),
                'mime' => 'text/plain',
                'meta' => [
                    'certificate' => collect($certificates)->map(fn ($item) => $item['title']),
                ],
            ],
            [
                'name' => 'hello multi cms 2',
                'data' => base64_encode(strrev($content)),
                'mime' => 'text/plain',
                'meta' => [
                    'certificate' => collect($certificates)->map(fn ($item) => $item['title']),
                ],
            ],
        ];

        $response = $this->prepareRequest($files, $prefix);

        $headers = $response['headers'];
        $message = $response['message'];

        $signatureService = new \Mitwork\Kalkan\Services\KalkanSignatureService();

        $ids = [];

        foreach ($message['documentsToSign'] as &$document) {

            foreach ($certificates as $certificate) {
                $status = $signatureService->signCms($document['document']['file']['data'], $certificate['content'], $certificate['password']);

                if ($status) {
                    $document['document']['file']['data'] = $status;
                    $ids[] = $document['id'];
                }
            }
        }

        // Отправка подписанных данных

        $response = $this->put($response['link'], $message, $headers);

        var_dump($files);

        $this->assertTrue($response->isOk(), sprintf('[%s] Ошибка обработки подписанных документов: %s', $prefix, $response->getContent()));

        // Проверка статуса после подписания

        foreach ($ids as $id) {
            $this->checkDocumentStatus($id, $prefix);
        }
    }

    public function testSingleXmlSigning(): void
    {
        $this->loadCertificates();

        $certificates = [$this->certificates[0]];

        foreach ($certificates as $certificate) {

            $content = Str::random(64);

            $data = [
                'name' => 'hello xml',
                'data' => sprintf('<test>%s</test>', $content),
                'mime' => 'text/xml',
                'meta' => [
                    'certificate' => $certificate['title'],
                ],
            ];

            $response = $this->prepareRequest([$data], $certificate['title']);

            $id = $response['id'];

            $headers = $response['headers'];
            $message = $response['message'];

            $signatureService = new \Mitwork\Kalkan\Services\KalkanSignatureService();

            foreach ($message['documentsToSign'] as &$document) {

                $status = $signatureService->signXml($document['documentXml'], $certificate['content'], $certificate['password']);

                if ($status) {
                    $document['documentXml'] = $status;
                }
            }

            // Отправка подписанных данных

            $response = $this->put($response['link'], $message, $headers);

            $this->assertTrue($response->isOk(), sprintf('[%s] Ошибка обработки подписанных документов: %s', $certificate['title'], $response->getContent()));

            $this->checkRequestStatus($id, $certificate['title']);
        }
    }

    public function testMultiXmlSigning(): void
    {
        $this->loadCertificates();

        $certificates = [$this->certificates[0]];

        foreach ($certificates as $certificate) {

            $content = Str::random(64);

            $files = [
                [
                    'name' => 'hello xml 1',
                    'data' => sprintf('<test>%s</test>', $content),
                    'mime' => 'text/xml',
                    'meta' => [
                        'certificate' => $certificate['title'],
                    ],
                ],
                [
                    'name' => 'hello xml 2',
                    'data' => sprintf('<test>%s</test>', strrev($content)),
                    'mime' => 'text/xml',
                    'meta' => [
                        'certificate' => $certificate['title'],
                    ],
                ],
            ];

            $response = $this->prepareRequest($files, $certificate['title']);

            $id = $response['id'];

            $headers = $response['headers'];
            $message = $response['message'];

            $signatureService = new \Mitwork\Kalkan\Services\KalkanSignatureService();

            foreach ($message['documentsToSign'] as &$document) {

                $status = $signatureService->signXml($document['documentXml'], $certificate['content'], $certificate['password']);

                if ($status) {
                    $document['documentXml'] = $status;
                }
            }

            // Отправка подписанных данных

            $response = $this->put($response['link'], $message, $headers);

            $this->assertTrue($response->isOk(), sprintf('[%s] Ошибка обработки подписанных документов: %s', $certificate['title'], $response->getContent()));

            $this->checkRequestStatus($id, $certificate['title']);
        }
    }

    public function testUniqueBearerServiceLinkCheck(): void
    {
        $this->loadCertificates();

        $certificates = [$this->certificates[0]];

        foreach ($certificates as $certificate) {

            $content = Str::random(64);

            $file = [
                'name' => 'hello bearer cms',
                'data' => base64_encode($content),
                'mime' => 'text/plain',
                'meta' => [
                    'certificate' => $certificate['title'],
                ],
            ];

            $auth = [
                'type' => AuthType::BEARER->value,
                'token' => '',
            ];

            $response = $this->prepareRequest([$file], $certificate['title'], $auth);

            $id = $response['id'];

            $headers = $response['headers'];
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

            $response = $this->put($response['link'], $message, $headers);

            Event::assertDispatched(AuthAccepted::class);

            $this->assertTrue($response->isOk(), sprintf('[%s] Ошибка обработки подписанных документов: %s', $certificate['title'], $response->getContent()));

            $this->checkRequestStatus($id, $certificate['title']);
        }
    }

    public function testHardcodedBearerServiceLinkCheck(): void
    {
        $this->loadCertificates();

        $certificates = [$this->certificates[0]];

        foreach ($certificates as $certificate) {

            $content = Str::random(64);
            $token = Str::random(32);

            $auth = [
                'type' => AuthType::BEARER->value,
                'token' => $token,
            ];

            $file = [
                'name' => 'hello bearer cms',
                'data' => base64_encode($content),
                'mime' => 'text/plain',
                'meta' => [
                    'certificate' => $certificate['title'],
                ],
            ];

            $response = $this->prepareRequest([$file], $certificate['title'], $auth);

            $headers = $response['headers'];
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

            $response = $this->put($response['link'], $message, $headers);

            Event::assertDispatched(DocumentSigned::class);

            $this->assertTrue($response->isOk(), sprintf('[%s] Ошибка обработки подписанных документов: %s', $certificate['title'], $response->getContent()));
        }
    }

    public function testWrongBearerServiceLinkReject(): void
    {
        $this->loadCertificates();

        $certificates = [$this->certificates[0]];

        foreach ($certificates as $certificate) {

            $content = Str::random(64);

            $file = [
                'name' => 'hello bearer cms',
                'data' => $content,
                'mime' => 'text/plain',
                'meta' => [
                    'certificate' => $certificate['title'],
                ],
            ];

            $auth = [
                'type' => AuthType::BEARER->value,
                'token' => Str::random(),
            ];

            $response = $this->prepareRequest([$file], $certificate['title'], $auth);

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

            Event::assertDispatched(AuthRejected::class);

            $this->assertTrue($response->status() === 401);
        }
    }
}
