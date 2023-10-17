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
use Mitwork\Kalkan\Events\DocumentRejected;
use Mitwork\Kalkan\Events\DocumentSaved;
use Mitwork\Kalkan\Events\DocumentSigned;
use Orchestra\Testbench\Concerns\WithWorkbench;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Mitwork\Kalkan\Services\IntegrationService::class)]
final class ApplicationTestingTest extends BaseTestCase
{
    use WithWorkbench;

    public function testSingleCmsSigning(): void
    {
        $this->loadCertificates();

        $certificates = $this->certificates;

        foreach ($certificates as $certificate) {

            $content = Str::random(64);

            $data = [
                'name' => 'hello cms',
                'data' => base64_encode($content),
                'meta' => [
                    'mime' => 'text/plain',
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

        $content = Str::random(64);
        $certificates = [$this->certificates[0], $this->certificates[1]];

        $file1 = [
            'name' => 'hello multi cms 1',
            'data' => base64_encode($content),
            'mime' => 'text/plain',
            'meta' => [
                'certificate' => collect($certificates)->map(fn ($item) => $item['title']),
            ],
        ];

        $file2 = [
            'name' => 'hello multi cms 2',
            'data' => base64_encode(strrev($content)),
            'mime' => 'text/plain',
            'meta' => [
                'certificate' => collect($certificates)->map(fn ($item) => $item['title']),
            ],
        ];

        $response = $this->prepareRequest([$file1, $file2], 'CMS Multi');

        $id = $response['id'];

        $headers = $response['headers'];
        $message = $response['message'];

        $signatureService = new \Mitwork\Kalkan\Services\KalkanSignatureService();

        $ids = [];

        foreach ($message['documentsToSign'] as &$document) {

            foreach ($certificates as $certificate) {
                $status = $signatureService->signCms($document['document']['file']['data'], $certificate['content'], $certificate['password']);

                if ($status) {
                    $document['document']['file']['data'] = $status;
                }

                $ids[] = $document['id'];
            }
        }

        // Отправка подписанных данных

        $response = $this->put($response['link'], $message, $headers);

        $this->assertTrue($response->isOk(), sprintf('[%s] Ошибка обработки подписанных документов: %s', 'CMS Multi', $response->getContent()));

        // Проверка статуса после подписания

        foreach ($ids as $id) {

            $response = $this->get(route(config('kalkan.actions.check-document'), $id));

            $this->assertTrue($response->isOk(), sprintf('[%s] Ошибка получения статуса документа %s: %s', $id, 'CMS Multi', $response->getContent()));
            $this->assertArrayHasKey('status', $response, sprintf('[%s] Ответ не содержит статус документа %s: %s', $id, 'CMS Multi', $response->getContent()));
            $this->assertTrue($response['status'] === DocumentStatus::SIGNED->value, sprintf('[%s] Некорректный статус для подписанного документа %s: %s', $id, 'CMS Multi', $response->getContent()));

        }
    }

    public function testSingleXmlSigning(): void
    {
        $this->loadCertificates();

        $certificates = $this->certificates;

        foreach ($certificates as $certificate) {

            $content = Str::random(64);

            $data = [
                'name' => 'hello xml',
                'data' => sprintf('<test>%s</test>', $content),
                'meta' => [
                    'mime' => 'text/xml',
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

        $certificates = $this->certificates;

        foreach ($certificates as $certificate) {

            $content = Str::random(64);

            $file1 = [
                'name' => 'hello xml 1',
                'data' => sprintf('<test>%s</test>', $content),
                'meta' => [
                    'mime' => 'text/xml',
                    'certificate' => $certificate['title'],
                ],
            ];

            $file2 = [
                'name' => 'hello xml 1',
                'data' => sprintf('<test>%s</test>', strrev($content)),
                'meta' => [
                    'mime' => 'text/xml',
                    'certificate' => $certificate['title'],
                ],
            ];

            $response = $this->prepareRequest([$file1, $file2], $certificate['title']);

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

    private function checkRequestStatus($id, $prefix = ''): void
    {
        // Проверка статуса после подписания

        $response = $this->get(route(config('kalkan.actions.check-request'), $id));

        $this->assertTrue($response->isOk(), sprintf('[%s] Ошибка получения статуса запроса %s: %s', $id, $prefix, $response->getContent()));
        $this->assertArrayHasKey('status', $response, sprintf('[%s] Ответ не содержит статус запроса %s: %s', $id, $prefix, $response->getContent()));
        $this->assertTrue($response['status'] === RequestStatus::PROCESSED->value, sprintf('[%s] Некорректный статус для обработанного запроса %s: %s', $id, $prefix, $response->getContent()));

    }

    public function testUniqueBearerServiceLink(): void
    {
        $this->loadCertificates();

        $certificates = $this->certificates;

        foreach ($certificates as $certificate) {

            $content = Str::random(64);

            $data = [
                'name' => 'hello bearer cms',
                'data' => base64_encode($content),
                'meta' => [
                    'mime' => 'text/plain',
                    'certificate' => $certificate['title'],
                ],
            ];

            $auth = [
                'type' => AuthType::BEARER->value,
                'token' => 'some-token-here',
            ];

            $response = $this->prepareRequest([$data], $certificate['title'], $auth);

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

    public function testHardcodedBearerServiceLink(): void
    {
        $this->loadCertificates();

        $certificates = $this->certificates;

        foreach ($certificates as $certificate) {

            $content = Str::random(64);

            $auth = [
                'type' => 'Bearer',
                'token' => 'some-hardcoded-token',
            ];

            $data = [
                'name' => 'hello bearer cms',
                'data' => base64_encode($content),
                'meta' => [
                    'mime' => 'text/plain',
                    'certificate' => $certificate['title'],
                ],
            ];

            $response = $this->prepareRequest([$data], $certificate['title'], $auth);

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

            Event::assertDispatched(DocumentSigned::class);

            $this->assertTrue($response->isOk(), sprintf('[%s] Ошибка обработки подписанных документов: %s', $certificate['title'], $response->getContent()));
        }
    }

    public function testWrongBearerServiceLink(): void
    {
        $this->loadCertificates();

        $certificates = $this->certificates;

        foreach ($certificates as $certificate) {

            $content = Str::random(64);

            $data = [
                'name' => 'hello bearer cms',
                'data' => $content,
                'meta' => [
                    'certificate' => $certificate['title'],
                ],
            ];

            $auth = [
                'type' => AuthType::BEARER->value,
                'token' => Str::random(),
            ];

            $response = $this->prepareRequest([$data], $certificate['title'], $auth);

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

    public function testDocumentRejection(): void
    {
        $this->loadCertificates(['active' => false]);

        $certificates = $this->certificates;

        foreach ($certificates as $certificate) {

            $content = Str::random(64);

            $data = [
                'name' => 'hello expired cms',
                'data' => base64_encode($content),
                'meta' => [
                    'certificate' => $certificate['title'],
                ],
            ];

            $response = $this->prepareRequest([$data], $certificate['title']);

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

            $response = $this->get(route(config('kalkan.actions.check-request'), $id));

            $this->assertTrue($response->isOk(), sprintf('[%s] Ошибка получения статуса запроса %s: %s', $id, $certificate['title'], $response->getContent()));
            $this->assertArrayHasKey('status', $response, sprintf('[%s] Ответ не содержит статус запроса %s: %s', $id, $certificate['title'], $response->getContent()));
            $this->assertTrue($response['status'] === RequestStatus::REJECTED->value, sprintf('[%s] Некорректный статус для ошибочного запроса %s: %s', $id, $certificate['title'], $response->getContent()));
        }
    }

    /**
     * Подготовка документ для тестирования
     *
     * @param  array  $files Данные документа
     * @param  string  $prefix Префикс теста
     * @return array Результаты
     */
    private function prepareRequest(array $files, string $prefix, array $auth = []): array
    {
        foreach ($files as &$file) {

            Event::fake();

            $response = $this->post(route(config('kalkan.actions.store-document')), $file);

            $this->assertTrue($response->isOk(), sprintf('[%s] Некорректный ответ статуса сохранения документа', $prefix));
            $this->assertArrayHasKey('id', $response, sprintf('[%s] Отсутствует идентификатор сохраненного документа', $prefix));

            $file['id'] = $response['id'];

            $id = $response['id'];

            // Проверка статуса до подписания

            $response = $this->get(route(config('kalkan.actions.check-document'), $id));

            $this->assertTrue($response->isOk(), sprintf('[%s] Ошибка получения статуса документа %s: %s', $id, $prefix, $response->getContent()));
            $this->assertArrayHasKey('status', $response, sprintf('[%s] Ответ не содержит статус документа %s: %s', $id, $prefix, $response->getContent()));
            $this->assertTrue($response['status'] === DocumentStatus::CREATED->value, sprintf('[%s] Некорректный статус для неподписанного документа %s: %s', $id, $prefix, $response->getContent()));

            Event::assertDispatched(DocumentSaved::class);
        }

        $data = [
            'description' => $prefix,
            'files' => $files,
            'auth' => $auth,
        ];

        $response = $this->post(route(config('kalkan.actions.store-request')), $data);

        $this->assertTrue($response->isOk(), sprintf('[%s] Некорректный ответ при сохранении запроса', $prefix));

        $id = $response['id'];
        $link = $response['url'];

        $response = $this->get(route(config('kalkan.actions.generate-qr-code'), ['id' => $id]));

        $this->assertTrue($response->isOk(), sprintf('[%s] Некорректный ответ при запросе QR-кода', $prefix));

        $this->assertArrayHasKey('uri', $response, sprintf('[%s] Отсутствует встраиваемое изображение', $prefix));
        $this->assertArrayHasKey('raw', $response, sprintf('[%s] Отсутствует исходное изображение', $prefix));

        $response = $this->get($link);

        $this->assertTrue($response->isOk(), sprintf('[%s] Отсутствуют сервисные данные', $prefix));

        $link = $response['document']['uri'];

        $headers = [];

        if (isset($response['document']['auth_type']) && $response['document']['auth_type'] === AuthType::BEARER->value) {
            $headers = ['Authorization' => sprintf('Bearer %s', $response['document']['auth_token'])];
        }

        $response = $this->get($link, $headers);

        $this->assertTrue($response->isOk(), sprintf('[%s] Отсутствует ссылка на документ', $prefix));

        return [
            'id' => $id,
            'headers' => $headers,
            'link' => $link,
            'message' => (array) $response->original,
        ];
    }
}
