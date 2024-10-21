<?php

namespace Mitwork\Kalkan\Contracts;

use Mitwork\Kalkan\Enums\RequestStatus;

interface RequestService
{
    /**
     * Добавление запроса для обработки
     *
     * @param  string|int  $id  Уникальный идентификатор
     * @param  array  $attributes  Содержимое и метаданные документа
     * @return bool Результат добавления
     */
    public function add(string|int $id, array $attributes, RequestStatus $status = RequestStatus::CREATED): bool;

    /**
     * Получение запроса
     *
     * @param  string|int  $id  Уникальный идентификатор
     * @return array|null Содержимое и метаданные документа
     */
    public function get(string|int $id): ?array;

    /**
     * Проверка статуса запроса
     *
     * @param  string|int  $id  Уникальный идентификатор
     * @return array|bool|null Результат проверки
     */
    public function check(string|int $id): array|bool|null;

    /**
     * Изменение статуса запроса
     */
    public function update(string|int $id, RequestStatus $status): void;

    /**
     * Обработка полученного запроса
     *
     * @param  string|int  $id  Уникальный идентификатор
     * @return bool Результат обработки
     */
    public function process(string|int $id, array $result, RequestStatus $status = RequestStatus::PROCESSED): bool;

    /**
     * Отклонение запроса
     *
     * @param  string|int  $id  Уникальный идентификатор
     * @param  string|null  $message  Сообщение или ошибка
     */
    public function reject(string|int $id, ?string $message = null): bool;
}
