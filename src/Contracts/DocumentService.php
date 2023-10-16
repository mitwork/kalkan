<?php

namespace Mitwork\Kalkan\Contracts;

interface DocumentService
{
    /**
     * Добавление документа для обработки
     *
     * @param  string|int  $id Уникальный идентификатор
     * @param  array  $content Содержимое и метаданные документа
     * @return bool Результат добавления
     */
    public function addDocument(string|int $id, array $content): bool;

    /**
     * Получение документа
     *
     * @param  string|int  $id Уникальный идентификатор
     * @return array|null Содержимое и метаданные документа
     */
    public function getDocument(string|int $id): ?array;

    /**
     * Проверка статуса подписания документа
     *
     * @param  string|int  $id Уникальный идентификатор
     * @return array|bool|null Результат проверки
     */
    public function checkDocument(string|int $id): array|bool|null;

    /**
     * Обработка подписанного документа
     *
     * @param  string|int  $id Уникальный идентификатор
     * @return bool Результат обработки
     */
    public function processDocument(string|int $id): bool;
}
