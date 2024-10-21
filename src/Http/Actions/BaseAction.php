<?php

namespace Mitwork\Kalkan\Http\Actions;

use Illuminate\Support\Facades\URL;

class BaseAction
{
    /**
     * Генерация подписанных ссылок
     *
     * @param  string  $route  Роут
     * @param  array  $params  Параметры
     * @param  int  $ttl  Время жизни
     * @return string Ссылка
     */
    public function generateSignedLink(string $route, array $params = [], int $ttl = 180): string
    {
        return URL::temporarySignedRoute($route, $ttl ?: config('kalkan.ttl'), $params);
    }

    /**
     * Генерация обычных ссылок
     *
     * @param  string  $route  Роут
     * @param  array  $params  Параметры
     * @return string Ссылка
     */
    public function generateTemporaryLink(string $route, array $params = []): string
    {
        return URL::route($route, $params);
    }
}
