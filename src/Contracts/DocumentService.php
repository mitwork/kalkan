<?php

namespace Mitwork\Kalkan\Contracts;

use Mitwork\Kalkan\Enums\DocumentStatus;

interface DocumentService
{
    /**
     * Добавление документа для обработки
     *
     * @param  string|int  $id  Уникальный идентификатор
     * @param  array  $attributes  Содержимое и метаданные документа
     * @return bool Результат добавления
     */
    public function add(string|int $id, array $attributes, DocumentStatus $status = DocumentStatus::CREATED): bool;

    /**
     * Получение документа
     *
     * @param  string|int  $id  Уникальный идентификатор
     * @return array|null Содержимое и метаданные документа
     */
    public function get(string|int $id): ?array;

    /**
     * Проверка статуса подписания документа
     *
     * @param  string|int  $id  Уникальный идентификатор
     * @return array|bool|null Результат проверки
     */
    public function check(string|int $id): array|bool|null;

    /**
     * Изменение статуса документа
     */
    public function update(string|int $id, DocumentStatus $status): void;

    /**
     * Обработка подписанного документа
     *
     * @param  string|int  $id  Уникальный идентификатор
     * @return bool Результат обработки
     */
    public function process(string|int $id, array $result, DocumentStatus $status = DocumentStatus::SIGNED): bool;

    /**
     * Отклонение документа
     *
     * @param  string|int  $id  Уникальный идентификатор
     * @param  string|null  $message  Сообщение или ошибка
     */
    public function reject(string|int $id, ?string $message = null): bool;
}
