<?php

namespace Mitwork\Kalkan\Http\Actions;

use Illuminate\Support\Facades\URL;

class BaseAction
{
    /**
     * Генерация временных ссылок
     *
     * @param  string  $route Роут
     * @param  array  $params Параметры
     * @param  int  $ttl Время жизни
     * @return string Ссылка
     */
    public function generateSignedLink(string $route, array $params = [], int $ttl = 30): string
    {
        return URL::temporarySignedRoute($route, $ttl ?: config('kalkan.options.ttl'), $params);
    }
}
