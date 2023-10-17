<?php

namespace Mitwork\Kalkan\Services;

use Illuminate\Support\Facades\Cache;
use Mitwork\Kalkan\Contracts\RequestService;
use Mitwork\Kalkan\Enums\RequestStatus;

class CacheRequestService implements RequestService
{
    /**
     * Добавление документа в кэш
     *
     * @param  string|int  $id Идентификатор
     * @param  array  $attributes Содержимое
     * @return bool Результат сохранения
     */
    public function add(string|int $id, array $attributes, RequestStatus $status = RequestStatus::CREATED): bool
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
    public function update($id, RequestStatus $status): void
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
    public function process(string|int $id, array $result = [], RequestStatus $status = RequestStatus::PROCESSED): bool
    {
        $document = Cache::get($id);

        if (! $document) {
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

        $document['status'] = RequestStatus::REJECTED;
        $document['result'] = null;
        $document['message'] = $message;

        return Cache::put($id, $document);
    }
}
