<?php

namespace Mitwork\Kalkan\Services;

use Mitwork\Kalkan\Enums\ContentType;
use Mitwork\Kalkan\Enums\SignatureType;

class IntegrationService
{
    protected array $meta = [];

    /**
     * @var array|array[]
     */
    protected array $documents = [
        'xml' => [],
        'cms' => [],
    ];

    /**
     * Подготовка данных для сервиса
     *
     * @param  string  $uri  Ссылка
     * @param  array  $request  Запрос
     * @return array Данные для сервиса
     */
    public function prepareServiceData(string $uri, array $request): array
    {
        return [
            'description' => $request['description'] ?? config('kalkan.options.description'),
            'expiry_date' => date('Y-m-d\TH:i:s.vP', time() + ($request['ttl'] ?? config('kalkan.ttl'))),
            'organisation' => $request['organisation'] ?? config('kalkan.options.organisation'),
            'document' => [
                'uri' => $uri,
                'auth_type' => $request['auth']['type'] ?? 'None',
                'auth_token' => $request['auth']['token'] ?? '',
            ],
        ];
    }

    /**
     * Добавление метаданных
     *
     * @param  string  $name  Ключ
     * @param  mixed  $value  Значение
     * @param  int|string|null  $key  Ключ документа
     */
    public function addMetaAttribute(string $name, mixed $value, int|string|null $key = null): void
    {
        if ($key) {
            $this->meta[$key][] = ['name' => $name, 'value' => $value];
        } else {
            $this->meta[] = ['name' => $name, 'value' => $value];
        }
    }

    /**
     * Получение метаданных
     *
     * @param  int|string  $key  Ключ документа
     * @return array Метаданные
     */
    public function getMetaAttributes(int|string $key, array $meta = []): array
    {

        if (isset($this->meta[$key])) {
            return $this->meta[$key];
        }

        return [];
    }

    /**
     * Добавление метаданных
     *
     * @param  string|int  $id  Идентификатор документа
     * @param  array  $meta  Атрибуты
     */
    public function addMetaAttributes(string|int $id, array $meta): void
    {
        if (count($meta) > 0) {
            foreach ($meta as $key => $value) {

                if (is_array($value)) {

                    if (isset($value['name']) && isset($value['value'])) {
                        $metaKey = $value['name'];
                        $metaValue = $value['value'];
                    } else {
                        $metaKey = key($value);
                        $metaValue = $value[key($value)];
                    }

                    $this->addMetaAttribute((string) $metaKey, (string) $metaValue, $id);
                } else {
                    $this->addMetaAttribute((string) $key, (string) $value, $id);
                }
            }
        }
    }

    /**
     * Добавление XML-документа
     *
     * @param  int|string  $id  Уникальный идентификатор
     * @param  string  $name  Наименование
     * @param  string  $data  Содержание
     * @param  array  $meta  Метаданные
     * @return array Данные документа
     */
    public function addXmlDocument(int|string $requestId, int|string $id, string $name, string $data, array $meta = []): array
    {
        $this->addMetaAttributes($id, $meta);

        $document = [
            'id' => is_numeric($id) ? (int) $id : $id,
            'nameRu' => $name,
            'nameKz' => $name,
            'nameEn' => $name,
            'meta' => $this->getMetaAttributes($id),
            'documentXml' => trim($data),
        ];

        if (count($document['meta']) === 0) {
            unset($document['meta']);
        }

        $this->documents['xml'][$requestId] = $document;

        return $document;
    }

    /**
     * Добавление CMS-документа
     *
     * @param  int|string  $id  Уникальный идентификатор
     * @param  string  $name  Наименование
     * @param  string  $data  Содержание
     * @param  array  $meta  Метаданные
     * @param  string  $mime  Тип файла
     * @return array Данные документа
     */
    public function addCmsDocument(int|string $requestId, int|string $id, string $name, string $data, array $meta = [], string $mime = '@file/pdf'): array
    {
        $this->addMetaAttributes($id, $meta);

        $document = [
            'id' => is_numeric($id) ? (int) $id : $id,
            'nameRu' => $name,
            'nameKz' => $name,
            'nameEn' => $name,
            'meta' => $this->getMetaAttributes($id),
            'document' => [
                'file' => [
                    'mime' => $mime === ContentType::PDF->value ? '@file/pdf' : $mime,
                    'data' => $data,
                ],
            ],
        ];

        if (count($document['meta']) === 0) {
            unset($document['meta']);
        }

        $this->documents['cms'][$requestId] = $document;

        return $document;
    }

    /**
     * Получение списка XML-документов
     */
    public function getXmlDocuments(string|int|null $key = null): array
    {
        return [
            'signMethod' => SignatureType::XML->value,
            'documentsToSign' => array_values($key ? [$this->documents['xml'][$key]] : $this->documents['xml']),
        ];
    }

    /**
     * Получение списка CMS-документов
     */
    public function getCmsDocuments(string|int|null $key = null): array
    {
        return [
            'signMethod' => SignatureType::CMS_WITH_DATA->value,
            'documentsToSign' => array_values($key ? [$this->documents['cms'][$key]] : $this->documents['cms']),
        ];
    }
}
