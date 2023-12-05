<?php

namespace Mitwork\Kalkan\Services;

use Illuminate\Support\Facades\Cache;
use Mitwork\Kalkan\Contracts\DocumentService;
use Mitwork\Kalkan\Enums\DocumentStatus;

class CacheDocumentService implements DocumentService
{
    /**
     * Ошибка
     *
     * @var string|null
     */
    public string|null $message = null;

    /**
     * Добавление документа в кэш
     *
     * @param  string|int  $id Идентификатор
     * @param  array  $attributes Содержимое
     * @return bool Результат сохранения
     */
    public function add(string|int $id, array $attributes, DocumentStatus $status = DocumentStatus::CREATED): bool
    {
        $attributes['status'] = $status;

        return Cache::add($id, $attributes, config('kalkan.ttl'));
    }

    /**
     * {@inheritDoc}
     */
    public function get(string|int $id): ?array
    {
        return Cache::get($id);
    }

    /**
     * {@inheritDoc}
     */
    public function check(string|int $id): array|bool|null
    {
        $document = Cache::get($id);

        if (! $document) {
            return null;
        }

        if (! isset($document['status'])) {
            return false;
        }

        return collect($document)->only(['status', 'result'])->all();
    }

    /**
     * {@inheritDoc}
     */
    public function update($id, DocumentStatus $status): void
    {
        $document = Cache::get($id);

        if (! $document) {
            return;
        }

        $document['status'] = $status;

        Cache::put($id, $document);
    }

    /**
     * {@inheritDoc}
     */
    public function process(string|int $id, array $result = [], DocumentStatus $status = DocumentStatus::SIGNED): bool
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

        if ($document['status'] === DocumentStatus::REJECTED) {

            if (isset($document['message']) && $document['message']) {
                $this->message = $document['message'];
            }

            return false;
        }

        $document['status'] = $status;
        $document['result'] = $result;
        $document['message'] = null;

        return Cache::put($id, $document);
    }

    /**
     * {@inheritDoc}
     */
    public function reject(int|string $id, string $message = null): bool
    {
        $document = Cache::get($id);

        if (! $document) {
            return false;
        }

        $document['status'] = DocumentStatus::REJECTED;
        $document['result'] = null;
        $document['message'] = $message;

        return Cache::put($id, $document);
    }
}
