<?php

namespace Mitwork\Kalkan\Traits;

trait TranslatesMessages
{
    /**
     * Получение локализованного сообщения
     *
     * Хелпер __() объявлен как string|array|null - группа переводов
     * возвращается массивом. Для сообщений пакета ожидается строка,
     * поэтому в остальных случаях отдаётся сам ключ.
     *
     * @param  string  $key  Ключ перевода
     * @param  array<string, mixed>  $replace  Подстановки
     * @return string Сообщение
     */
    protected static function message(string $key, array $replace = []): string
    {
        $message = __($key, $replace);

        return is_string($message) ? $message : $key;
    }
}
