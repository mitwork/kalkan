<?php

namespace Mitwork\Kalkan\Services;

use Illuminate\Support\Facades\Cache;
use Mitwork\Kalkan\Contracts\DocumentService;

class CacheDocumentService implements DocumentService
{
    /**
     * Добавление документа в кэш
     *
     * @param  string|int  $id Идентификатор
     * @param  array  $content Содержимое
     * @return bool Результат сохранения
     */
    public function addDocument(string|int $id, array $content): bool
    {
        return Cache::add($id, $content, config('kalkan.options.ttl'));
    }

    /**
     * Получение документа
     *
     * @param  string|int  $id Идентификатор
     * @return array|null Содержимое
     */
    public function getDocument(string|int $id): ?array
    {
        return Cache::get($id);
    }

    /**
     * Проверка статус подписания документа
     *
     * @param  string|int  $id Идентификатор
     * @return bool|null Результат
     */
    public function checkDocument(string|int $id): ?bool
    {
        $document = Cache::get($id);

        if (! $document) {
            return null;
        }

        return isset($document['status']) && $document['status'] === true;
    }

    /**
     * Обработка документа
     *
     * @param  string|int  $id Идентификатор
     * @return bool Статус обработки
     */
    public function processDocument(string|int $id): bool
    {
        if (isset($this->documents['cms'][$id])) {
            unset($this->documents['cms'][$id]);
        } elseif (isset($this->documents['xml'][$id])) {
            unset($this->documents['xml'][$id]);
        }

        $document = Cache::get($id);

        if (! $document) {
            return false;
        }

        $document['status'] = true;

        return Cache::put($id, $document);
    }
}
