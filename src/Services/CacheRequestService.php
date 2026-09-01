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
     * @param  string|int  $id  Идентификатор
     * @param  array<string, mixed>  $attributes  Содержимое
     * @return bool Результат сохранения
     */
    public function add(string|int $id, array $attributes, RequestStatus $status = RequestStatus::CREATED): bool
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
    public function update(string|int $id, RequestStatus $status): void
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
    public function process(string|int $id, array $result = [], RequestStatus $status = RequestStatus::PROCESSED): bool
    {
        $document = Cache::get((string) $id);

        if (! $document) {
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

        $document['status'] = RequestStatus::REJECTED;
        $document['result'] = null;
        $document['message'] = $message;

        return Cache::put((string) $id, $document);
    }
}
