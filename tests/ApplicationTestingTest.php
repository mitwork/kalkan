<?php

declare(strict_types=1);

namespace Mitwork\Kalkan\Tests;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Mitwork\Kalkan\Enums\AuthType;
use Mitwork\Kalkan\Enums\ContentType;
use Mitwork\Kalkan\Events\AuthAccepted;
use Mitwork\Kalkan\Events\AuthRejected;
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
                'content' => base64_encode($content),
                'type' => ContentType::CMS->value,
                'meta' => [
                    'certificate' => $certificate['title'],
                ],
            ];

            $response = $this->prepareDocument($data, $certificate['title']);

            $id = $response['id'];

            $headers = $response['headers'];
            $message = $response['message'];

            $signatureService = new \Mitwork\Kalkan\Services\KalkanSignatureService();

            foreach ($message['documentsToSign'] as &$document) {

                $status = $signatureService->signCms($document['documentCms'], $certificate['content'], $certificate['password']);

                if ($status) {
                    $document['documentCms'] = $status;
                }
            }

            // Отправка подписанных данных

            $response = $this->put($response['link'], $message, $headers);

            $this->assertTrue($response->isOk(), sprintf('[%s] Ошибка обработки подписанных документов: %s', $certificate['title'], $response->getContent()));

            // Проверка статуса после подписания

            $response = $this->get(route(config('kalkan.actions.check-document'), $id));

            $this->assertTrue($response->isOk(), sprintf('[%s] Ошибка получения статуса документа %s: %s', $id, $certificate['title'], $response->getContent()));
            $this->assertArrayHasKey('status', $response, sprintf('[%s] Ответ не содержит статус документа %s: %s', $id, $certificate['title'], $response->getContent()));
            $this->assertTrue($response['status'], sprintf('[%s] Некорректный статус для подписанного документа %s: %s', $id, $certificate['title'], $response->getContent()));
        }
    }

    public function testMultiCmsSigning(): void
    {
        $this->loadCertificates();

        $content = Str::random(64);
        $certificates = [$this->certificates[0], $this->certificates[1]];

        $data = [
            'name' => 'hello multi cms',
            'content' => base64_encode($content),
            'type' => ContentType::CMS->value,
            'meta' => [
                'certificate' => collect($certificates)->map(fn ($item) => $item['title']),
            ],
        ];

        $response = $this->prepareDocument($data, 'CMS Multi');

        $id = $response['id'];

        $headers = $response['headers'];
        $message = $response['message'];

        $signatureService = new \Mitwork\Kalkan\Services\KalkanSignatureService();

        foreach ($message['documentsToSign'] as &$document) {

            foreach ($certificates as $certificate) {
                $status = $signatureService->signCms($document['documentCms'], $certificate['content'], $certificate['password']);

                if ($status) {
                    $document['documentCms'] = $status;
                }
            }
        }

        // Отправка подписанных данных

        $response = $this->put($response['link'], $message, $headers);
        $this->assertTrue($response->isOk(), sprintf('[%s] Ошибка обработки подписанных документов: %s', 'CMS Multi', $response->getContent()));

        // Проверка статуса после подписания

        $response = $this->get(route(config('kalkan.actions.check-document'), $id));

        $this->assertTrue($response->isOk(), sprintf('[%s] Ошибка получения статуса документа %s: %s', $id, 'CMS Multi', $response->getContent()));
        $this->assertArrayHasKey('status', $response, sprintf('[%s] Ответ не содержит статус документа %s: %s', $id, 'CMS Multi', $response->getContent()));
        $this->assertTrue($response['status'], sprintf('[%s] Некорректный статус для подписанного документа %s: %s', $id, 'CMS Multi', $response->getContent()));

    }

    public function testSingleXmlSigning(): void
    {
        $this->loadCertificates();

        $certificates = $this->certificates;

        foreach ($certificates as $certificate) {

            $content = Str::random(64);

            $data = [
                'name' => 'hello xml',
                'content' => sprintf('<test>%s</test>', $content),
                'type' => ContentType::XML->value,
                'meta' => [
                    'certificate' => $certificate['title'],
                ],
            ];

            $response = $this->prepareDocument($data, $certificate['title']);

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

            // Проверка статуса после подписания

            $response = $this->get(route(config('kalkan.actions.check-document'), $id));

            $this->assertTrue($response->isOk(), sprintf('[%s] Ошибка получения статуса документа %s: %s', $id, $certificate['title'], $response->getContent()));
            $this->assertArrayHasKey('status', $response, sprintf('[%s] Ответ не содержит статус документа %s: %s', $id, $certificate['title'], $response->getContent()));
            $this->assertTrue($response['status'], sprintf('[%s] Некорректный статус для подписанного документа %s: %s', $id, $certificate['title'], $response->getContent()));
        }
    }

    public function testUniqueBearerServiceLink(): void
    {
        $this->loadCertificates();

        $this->app['config']->set('kalkan.options.auth.type', AuthType::BEARER->value);

        $certificates = $this->certificates;

        foreach ($certificates as $certificate) {

            $content = Str::random(64);

            $data = [
                'name' => 'hello bearer cms',
                'content' => base64_encode($content),
                'type' => ContentType::CMS->value,
                'meta' => [
                    'certificate' => $certificate['title'],
                ],
            ];

            $response = $this->prepareDocument($data, $certificate['title']);

            $id = $response['id'];

            $headers = $response['headers'];
            $message = $response['message'];

            $signatureService = new \Mitwork\Kalkan\Services\KalkanSignatureService();

            foreach ($message['documentsToSign'] as &$document) {

                $status = $signatureService->signCms($document['documentCms'], $certificate['content'], $certificate['password']);

                if ($status) {
                    $document['documentCms'] = $status;
                }
            }

            // Отправка подписанных данных

            Event::fake();

            $response = $this->put($response['link'], $message, $headers);

            Event::assertDispatched(AuthAccepted::class);

            $this->assertTrue($response->isOk(), sprintf('[%s] Ошибка обработки подписанных документов: %s', $certificate['title'], $response->getContent()));

            // Проверка статуса после подписания

            $response = $this->get(route(config('kalkan.actions.check-document'), $id));

            $this->assertTrue($response->isOk(), sprintf('[%s] Ошибка получения статуса документа %s: %s', $id, $certificate['title'], $response->getContent()));
            $this->assertArrayHasKey('status', $response, sprintf('[%s] Ответ не содержит статус документа %s: %s', $id, $certificate['title'], $response->getContent()));
            $this->assertTrue($response['status'], sprintf('[%s] Некорректный статус для подписанного документа %s: %s', $id, $certificate['title'], $response->getContent()));
        }
    }

    public function testHardcodedBearerServiceLink(): void
    {
        $this->loadCertificates();

        $this->app['config']->set('kalkan.options.auth.type', AuthType::BEARER->value);
        $this->app['config']->set('kalkan.options.auth.token', 'some-hardcoded-token');

        $certificates = $this->certificates;

        foreach ($certificates as $certificate) {

            $content = Str::random(64);

            $data = [
                'name' => 'hello bearer cms',
                'content' => base64_encode($content),
                'type' => ContentType::CMS->value,
                'meta' => [
                    'certificate' => $certificate['title'],
                ],
            ];

            $response = $this->prepareDocument($data, $certificate['title']);

            $id = $response['id'];

            $headers = $response['headers'];
            $message = $response['message'];

            $signatureService = new \Mitwork\Kalkan\Services\KalkanSignatureService();

            foreach ($message['documentsToSign'] as &$document) {

                $status = $signatureService->signCms($document['documentCms'], $certificate['content'], $certificate['password']);

                if ($status) {
                    $document['documentCms'] = $status;
                }
            }

            // Отправка подписанных данных

            Event::fake();

            $response = $this->put($response['link'], $message, $headers);

            Event::assertDispatched(DocumentSigned::class);

            $this->assertTrue($response->isOk(), sprintf('[%s] Ошибка обработки подписанных документов: %s', $certificate['title'], $response->getContent()));

            // Проверка статуса после подписания

            $response = $this->get(route(config('kalkan.actions.check-document'), $id));

            $this->assertTrue($response->isOk(), sprintf('[%s] Ошибка получения статуса документа %s: %s', $id, $certificate['title'], $response->getContent()));
            $this->assertArrayHasKey('status', $response, sprintf('[%s] Ответ не содержит статус документа %s: %s', $id, $certificate['title'], $response->getContent()));
            $this->assertTrue($response['status'], sprintf('[%s] Некорректный статус для подписанного документа %s: %s', $id, $certificate['title'], $response->getContent()));
        }
    }

    public function testWrongBearerServiceLink(): void
    {
        $this->loadCertificates();

        $this->app['config']->set('kalkan.options.auth.type', AuthType::BEARER->value);

        $certificates = $this->certificates;

        foreach ($certificates as $certificate) {

            $content = Str::random(64);

            $data = [
                'name' => 'hello bearer cms',
                'content' => base64_encode($content),
                'type' => ContentType::CMS->value,
                'meta' => [
                    'certificate' => $certificate['title'],
                ],
            ];

            $response = $this->prepareDocument($data, $certificate['title']);

            $message = $response['message'];

            $signatureService = new \Mitwork\Kalkan\Services\KalkanSignatureService();

            foreach ($message['documentsToSign'] as &$document) {

                $status = $signatureService->signCms($document['documentCms'], $certificate['content'], $certificate['password']);

                if ($status) {
                    $document['documentCms'] = $status;
                }
            }

            // Отправка подписанных данных

            Event::fake();

            $response = $this->put($response['link'], $message);

            Event::assertDispatched(AuthRejected::class);

            $this->assertTrue($response->status() === 401);
        }
    }

    /**
     * Подготовка документ для тестирования
     *
     * @param  array  $data Данные документа
     * @param  string  $prefix Префикс теста
     * @return array Результаты
     */
    private function prepareDocument(array $data, string $prefix): array
    {
        Event::fake();

        $response = $this->post(route(config('kalkan.actions.store-document')), $data);

        $this->assertTrue($response->isOk(), sprintf('[%s] Некорректный ответ статуса сохранения документа', $prefix));
        $this->assertArrayHasKey('id', $response, sprintf('[%s] Отсутствует идентификатор сохраненного документа', $prefix));

        Event::assertDispatched(DocumentSaved::class);

        $id = $response['id'];
        $link = $response['url'];

        // Проверка статуса до подписания

        $response = $this->get(route(config('kalkan.actions.check-document'), $id));

        $this->assertTrue($response->isOk(), sprintf('[%s] Ошибка получения статуса документа %s: %s', $id, $prefix, $response->getContent()));
        $this->assertArrayHasKey('status', $response, sprintf('[%s] Ответ не содержит статус документа %s: %s', $id, $prefix, $response->getContent()));
        $this->assertFalse($response['status'], sprintf('[%s] Некорректный статус для неподписанного документа %s: %s', $id, $prefix, $response->getContent()));

        $response = $this->get(route(config('kalkan.actions.generate-cross-links'), ['id' => $id]));

        $this->assertTrue($response->isOk(), sprintf('[%s] Некорректный ответ при формировании cross-ссылок', $prefix));

        $response = $this->get(route(config('kalkan.actions.generate-qr-code'), ['id' => $id]));

        $this->assertTrue($response->isOk(), sprintf('[%s] Некорректный ответ при запросе QR-кода', $prefix));

        $this->assertArrayHasKey('uri', $response, sprintf('[%s] Отсутствует изображение', $prefix));
        $this->assertArrayHasKey('raw', $response, sprintf('[%s] Отсутствует ссылка', $prefix));

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
