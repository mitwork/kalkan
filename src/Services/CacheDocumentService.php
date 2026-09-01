<?php

namespace Mitwork\Kalkan\Services;

use Illuminate\Support\Facades\Cache;
use Mitwork\Kalkan\Contracts\DocumentService;
use Mitwork\Kalkan\Enums\DocumentStatus;

class CacheDocumentService implements DocumentService
{
    /**
     * Ошибка
     */
    public ?string $message = null;

    /**
     * Добавление документа в кэш
     *
     * @param  string|int  $id  Идентификатор
     * @param  array<string, mixed>  $attributes  Содержимое
     * @return bool Результат сохранения
     */
    public function add(string|int $id, array $attributes, DocumentStatus $status = DocumentStatus::CREATED): bool
    {
        $attributes['status'] = $status;

        return Cache::add((string) $id, $attributes, config('kalkan.ttl'));
    }

    /**
     * {@inheritDoc}
     */
    public function get(string|int $id): ?array
    {
        return Cache::get((string) $id);
    }

    /**
     * {@inheritDoc}
     */
    public function check(string|int $id): array|bool|null
    {
        $document = Cache::get((string) $id);

        if (! is_array($document)) {
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
    public function update(string|int $id, DocumentStatus $status): void
    {
        $document = Cache::get((string) $id);

        if (! $document) {
            return;
        }

        $document['status'] = $status;

        Cache::put((string) $id, $document);
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

        $document = Cache::get((string) $id);

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

        return Cache::put((string) $id, $document);
    }

    /**
     * {@inheritDoc}
     */
    public function reject(int|string $id, ?string $message = null): bool
    {
        $document = Cache::get((string) $id);

        if (! $document) {
            return false;
        }

        $document['status'] = DocumentStatus::REJECTED;
        $document['result'] = null;
        $document['message'] = $message;

        return Cache::put((string) $id, $document);
    }
}
