<?php

namespace Mitwork\Kalkan\Services;

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
     * @param  string  $url Ссылка
     * @param  string  $authType Тип аутентификации
     * @param  string  $authToken Токен аутентификации
     * @return array Данные для сервиса
     */
    public function prepareServiceData(string $url, string $authType = 'None', string $authToken = ''): array
    {
        $options = config('kalkan.options');

        return [
            'description' => $options['description'],
            'expiry_date' => date('c', time() + $options['ttl']),
            'organisation' => $options['organisation'],
            'document' => [
                'uri' => $url,
                'auth_type' => $authType,
                'auth_token' => $authToken,
            ],
        ];
    }

    /**
     * Добавление метаданных
     *
     * @param  string  $name Ключ
     * @param  mixed  $value Значение
     * @param  int|string|null  $key Ключ документа
     */
    public function addMetaAttribute(string $name, mixed $value, int|string $key = null): void
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
     * @param  int|string  $key Ключ документа
     * @return array Метаданные
     */
    public function getMetaAttributes(int|string $key): array
    {
        if (isset($this->meta[$key])) {
            return $this->meta[$key];
        }

        return [];
    }

    /**
     * Добавление XML-документа
     *
     * @param  int|string  $id Уникальный идентификатор
     * @param  string  $name Наименование
     * @param  string  $content Содержание
     * @param  array  $meta Метаданные
     * @return array Данные документа
     */
    public function addXmlDocument(int|string $id, string $name, string $content, array $meta = []): array
    {
        $mime = '';

        if (count($meta) > 0) {
            foreach ($meta as $key => $value) {

                if (is_array($value)) {

                    $metaKey = key($value);
                    $metaValue = $value[key($value)];

                    if ($metaKey === 'mime') {
                        $mime = $metaValue;

                        continue;
                    }

                    $this->addMetaAttribute($metaKey, $metaValue, $id);
                } else {
                    $this->addMetaAttribute($key, $value, $id);
                }
            }
        }

        $document = [
            'id' => $id,
            'nameRu' => $name,
            'nameKz' => $name,
            'nameEn' => $name,
            'meta' => $this->getMetaAttributes($id),
            'documentXml' => $content,
        ];

        $this->documents['xml'][$id] = $document;

        return $document;
    }

    /**
     * Добавление CMS-документа
     *
     * @param  int|string  $id Уникальный идентификатор
     * @param  string  $name Наименование
     * @param  string  $content Содержание
     * @param  array  $meta Метаданные
     * @return array Данные документа
     */
    public function addCmsDocument(int|string $id, string $name, string $content, array $meta = []): array
    {
        $mime = '';

        if (count($meta) > 0) {
            foreach ($meta as $key => $value) {

                if (is_array($value)) {

                    $metaKey = key($value);
                    $metaValue = $value[key($value)];

                    if ($metaKey === 'mime') {
                        $mime = $metaValue;

                        continue;
                    }

                    $this->addMetaAttribute($metaKey, $metaValue, $id);
                } else {
                    $this->addMetaAttribute($key, $value, $id);
                }
            }
        }

        // @todo provide real mime or file extensions
        // @see debug results

        $document = [
            'id' => $id,
            'nameRu' => $name,
            'nameKz' => $name,
            'nameEn' => $name,
            'meta' => $this->getMetaAttributes($id),
            'document' => [
                'file' => [
                    'mime' => '@file/pdf',
                    'data' => $content,
                ],
            ],
        ];

        $this->documents['cms'][$id] = $document;

        return $document;
    }

    /**
     * Получение списка XML-документов
     */
    public function getXmlDocuments(string|int $key = null): array
    {
        return [
            'signMethod' => SignatureType::XML->value,
            'documentsToSign' => array_values($key ? [$this->documents['xml'][$key]] : $this->documents['xml']),
        ];
    }

    /**
     * Получение списка CMS-документов
     */
    public function getCmsDocuments(string|int $key = null): array
    {
        return [
            'signMethod' => SignatureType::CMS_WITH_DATA->value,
            'documentsToSign' => array_values($key ? [$this->documents['cms'][$key]] : $this->documents['cms']),
        ];
    }
}
