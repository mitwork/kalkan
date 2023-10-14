<?php

declare(strict_types=1);

namespace Mitwork\Kalkan\Tests;

use Illuminate\Support\Str;
use Mitwork\Kalkan\Enums\ContentType;
use Orchestra\Testbench\Concerns\WithWorkbench;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Mitwork\Kalkan\Services\IntegrationService::class)]
final class ApplicationTestingTest extends BaseTestCase
{
    use WithWorkbench;

    public function testSingleCmsSigning(): void
    {
        $this->loadCertificates('sign');

        // $this->app['config']->set('kalkan.options.ttl', '1');

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

            $message = $response['message'];
            $id = $response['id'];

            $signatureService = new \Mitwork\Kalkan\Services\KalkanSignatureService();

            foreach ($message['documentsToSign'] as &$document) {

                $status = $signatureService->signCms($document['documentCms'], $certificate['content'], $certificate['password']);

                if ($status) {
                    $document['documentCms'] = $status;
                }
            }

            // Отправка подписанных данных

            $response = $this->put($response['link'], $message);

            $this->assertTrue($response->isOk(), sprintf('[%s] Ошибка обработки подписанных документов: %s', $certificate['title'], $response->getContent()));

            // Проверка статуса после подписания

            $response = $this->get(route('check-document', $id));

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

        $message = $response['message'];
        $id = $response['id'];

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

        $response = $this->put($response['link'], $message);
        $this->assertTrue($response->isOk(), sprintf('[%s] Ошибка обработки подписанных документов: %s', 'CMS Multi', $response->getContent()));

        // Проверка статуса после подписания

        $response = $this->get(route('check-document', $id));

        $this->assertTrue($response->isOk(), sprintf('[%s] Ошибка получения статуса документа %s: %s', $id, 'CMS Multi', $response->getContent()));
        $this->assertArrayHasKey('status', $response, sprintf('[%s] Ответ не содержит статус документа %s: %s', $id, 'CMS Multi', $response->getContent()));
        $this->assertTrue($response['status'], sprintf('[%s] Некорректный статус для подписанного документа %s: %s', $id, 'CMS Multi', $response->getContent()));

    }

    public function testSingleXmlSigning(): void
    {
        $this->loadCertificates('sign');

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

            $message = $response['message'];
            $id = $response['id'];

            $signatureService = new \Mitwork\Kalkan\Services\KalkanSignatureService();

            foreach ($message['documentsToSign'] as &$document) {

                $status = $signatureService->signXml($document['documentXml'], $certificate['content'], $certificate['password']);

                if ($status) {
                    $document['documentXml'] = $status;
                }
            }

            // Отправка подписанных данных

            $response = $this->put($response['link'], $message);

            $this->assertTrue($response->isOk(), sprintf('[%s] Ошибка обработки подписанных документов: %s', $certificate['title'], $response->getContent()));

            // Проверка статуса после подписания

            $response = $this->get(route('check-document', $id));

            $this->assertTrue($response->isOk(), sprintf('[%s] Ошибка получения статуса документа %s: %s', $id, $certificate['title'], $response->getContent()));
            $this->assertArrayHasKey('status', $response, sprintf('[%s] Ответ не содержит статус документа %s: %s', $id, $certificate['title'], $response->getContent()));
            $this->assertTrue($response['status'], sprintf('[%s] Некорректный статус для подписанного документа %s: %s', $id, $certificate['title'], $response->getContent()));
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
        $response = $this->post(route('store-document'), $data);

        $this->assertTrue($response->isOk(), sprintf('[%s] Некорректный ответ статуса сохранения документа', $prefix));
        $this->assertArrayHasKey('id', $response, sprintf('[%s] Отсутствует идентификатор сохраненного документа', $prefix));

        $id = $response['id'];

        // Проверка статуса после подписания

        $response = $this->get(route('check-document', $id));

        $this->assertTrue($response->isOk(), sprintf('[%s] Ошибка получения статуса документа %s: %s', $id, $prefix, $response->getContent()));
        $this->assertArrayHasKey('status', $response, sprintf('[%s] Ответ не содержит статус документа %s: %s', $id, $prefix, $response->getContent()));
        $this->assertFalse($response['status'], sprintf('[%s] Некорректный статус для неподписанного документа %s: %s', $id, $prefix, $response->getContent()));

        $response = $this->get(route('generate-cross-link', ['id' => $id]));

        $this->assertTrue($response->isOk(), sprintf('[%s] Некорректный ответ при формировании cross-ссылок', $prefix));

        $response = $this->get(route('generate-qr-code', ['id' => $id]));

        $this->assertTrue($response->isOk(), sprintf('[%s] Некорректный ответ при запросе QR-кода', $prefix));

        $this->assertArrayHasKey('image', $response, 'Отсутствует изображение');
        $this->assertArrayHasKey('link', $response, 'Отсутствует ссылка');

        $link = $response['link'];

        $response = $this->get($link);

        $this->assertTrue($response->isOk(), sprintf('[%s] Отсутствуют метаданные', $prefix));

        $link = $response['document']['uri'];

        $response = $this->get($link);
        $this->assertTrue($response->isOk(), sprintf('[%s] Отсутствует ссылка на документ', $prefix));

        return [
            'id' => $id,
            'message' => (array) $response->original,
            'link' => $link,
        ];
    }
}
