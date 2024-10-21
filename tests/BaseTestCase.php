<?php

declare(strict_types=1);

namespace Mitwork\Kalkan\Tests;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Mitwork\Kalkan\Enums\AuthType;
use Mitwork\Kalkan\Enums\DocumentStatus;
use Mitwork\Kalkan\Enums\RequestStatus;
use Mitwork\Kalkan\Events\DocumentSaved;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase;
use Symfony\Component\Yaml\Yaml;

class BaseTestCase extends TestCase
{
    use WithWorkbench;

    protected Collection $certificates;

    /**
     * Загрузка сертификатов
     *
     * @param  array  $filters  Фильтры
     */
    public function loadCertificates(array $filters = ['active' => true, 'policy' => 'sign']): void
    {
        $certificates = Yaml::parseFile(__DIR__.'/certificates.yml')['certificates'];

        $this->certificates = collect($certificates)->filter(function ($item) use ($filters) {
            foreach ($filters as $key => $value) {
                if ($item[$key] !== $value) {
                    return false;
                }
            }

            return true;
        })->values();
    }

    /**
     * Подготовка документа для тестирования
     *
     * @param  array  $files  Данные документа
     * @param  string  $prefix  Префикс теста
     * @return array Результаты
     */
    public function prepareRequest(array $files, string $prefix = '', array $auth = []): array
    {
        foreach ($files as &$file) {

            Event::fake();

            $response = $this->post(route(config('kalkan.actions.store-document')), $file);

            Event::assertDispatched(DocumentSaved::class);

            $this->assertTrue($response->isOk(), sprintf('[%s] Некорректный ответ статуса сохранения документа', $prefix));
            $this->assertArrayHasKey('id', $response, sprintf('[%s] Отсутствует идентификатор сохраненного документа', $prefix));

            $file['id'] = $response['id'];

            $id = $response['id'];

            // Проверка статуса до подписания

            $response = $this->get(route(config('kalkan.actions.check-document'), $id));

            $this->assertTrue($response->isOk(), sprintf('[%s] Ошибка получения статуса документа %s: %s', $id, $prefix, $response->getContent()));
            $this->assertArrayHasKey('status', $response, sprintf('[%s] Ответ не содержит статус документа %s: %s', $id, $prefix, $response->getContent()));
            $this->assertTrue($response['status'] === DocumentStatus::CREATED->value, sprintf('[%s] Некорректный статус для неподписанного документа %s: %s', $id, $prefix, $response->getContent()));
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

    /**
     * Подписание документа
     *
     * @param  array  $message  Сообщение
     * @param  array  $certificate  Сертификат
     */
    public function signResponse(array &$message, array $certificate): void
    {
        $signatureService = new \Mitwork\Kalkan\Services\KalkanSignatureService;

        foreach ($message['documentsToSign'] as &$document) {

            if (isset($document['documentXml'])) {

                $status = $signatureService->signXml($document['documentXml'], $certificate['content'], $certificate['password']);

                if ($status) {
                    $document['documentXml'] = $status;
                }
            } else {

                $status = $signatureService->signCms($document['document']['file']['data'], $certificate['content'], $certificate['password']);

                if ($status) {
                    $document['document']['file']['data'] = $status;
                }
            }
        }
    }

    /**
     * Проверка статуса запроса
     *
     * @param  string  $id  Идентификатор запроса
     * @param  string  $prefix  Префикс события
     * @param  RequestStatus  $expectedStatus  Ожидаемый статус
     */
    public function checkRequestStatus(string $id, string $prefix = '', RequestStatus $expectedStatus = RequestStatus::PROCESSED): void
    {
        if ($prefix) {
            $prefix = '['.$prefix.']';
        }

        $response = $this->get(route(config('kalkan.actions.check-request'), $id));

        $this->assertTrue($response->isOk(), trim(sprintf('%s Ошибка получения статуса запроса %s: %s', $id, $prefix, $response->getContent())));
        $this->assertArrayHasKey('status', $response, trim(sprintf('%s Ответ не содержит статус запроса %s: %s', $id, $prefix, $response->getContent())));
        $this->assertEquals($expectedStatus->value, $response['status'], trim(sprintf('%s Некорректный статус запроса %s: %s', $id, $prefix, $response->getContent())));

    }

    /**
     * Проверка статуса документа
     *
     * @param  string|int  $id  Идентификатор документа
     * @param  string  $prefix  Префикс события
     * @param  DocumentStatus  $expectedStatus  Ожидаемый статус
     */
    public function checkDocumentStatus(string|int $id, string $prefix = '', DocumentStatus $expectedStatus = DocumentStatus::SIGNED): void
    {
        if ($prefix) {
            $prefix = '['.$prefix.']';
        }

        $response = $this->get(route(config('kalkan.actions.check-document'), $id));

        $this->assertTrue($response->isOk(), trim(sprintf('%s Ошибка получения статуса документа %s: %s', $id, $prefix, $response->getContent())));
        $this->assertArrayHasKey('status', $response, trim(sprintf('%s Ответ не содержит статус документа %s: %s', $id, $prefix, $response->getContent())));
        $this->assertEquals($expectedStatus->value, $response['status'], trim(sprintf('%s Некорректный статус документа %s: %s', $id, $prefix, $response->getContent())));
    }
}
