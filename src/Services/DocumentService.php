<?php

namespace Mitwork\Kalkan\Services;

class DocumentService
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
     * @param  array  $params Параметры
     * @return array Данные для сервиса
     */
    public function prepareServiceData(string $url, array $params = []): array
    {
        $options = config('kalkan.options');

        $service = [
            'description' => $options['description'],
            'expiry_date' => date('c', time() + $options['ttl']),
            'organisation' => $options['organisation'],
            'document' => [
                'uri' => $url,
                'auth_type' => 'None',
                'auth_token' => '',
            ],
        ] + $params;

        return $service;

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
            $this->meta[$key] = ['name' => $name, 'value' => $value];
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

        if (count($meta) > 0) {
            foreach ($meta as $key => $value) {
                $this->addMetaAttribute($key, $value, $id);
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

        if (count($meta) > 0) {
            foreach ($meta as $key => $value) {
                $this->addMetaAttribute($key, $value, $id);
            }
        }

        $document = [
            'id' => $id,
            'nameRu' => $name,
            'nameKz' => $name,
            'nameEn' => $name,
            'meta' => $this->getMetaAttributes($id),
            'documentCms' => $content,
        ];

        $this->documents['cms'][$id] = $document;

        return $document;
    }

    /**
     * Получение списка XML-документов
     */
    public function getXmlDocuments(): array
    {
        return [
            'signMethod' => 'XML',
            'documentsToSign' => array_values($this->documents['xml']),
        ];
    }

    /**
     * Получение списка CMS-документов
     */
    public function getCmsDocuments(): array
    {
        return [
            'signMethod' => 'CMS',
            'documentsToSign' => array_values($this->documents['cms']),
        ];
    }
}