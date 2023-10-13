<?php

namespace Mitwork\Kalkan\Services;

class BaseService
{
    public array $response = [];

    public ?string $error = null;

    /**
     * Получение результата
     *
     * @return array Результат
     */
    public function getResponse(): array
    {
        return $this->response;
    }

    /**
     * Сохранение результата
     *
     * @param  array  $response Результат
     */
    protected function setResponse(array $response): void
    {
        $this->response = $response;
    }

    /**
     * Получение ошибки
     *
     * @return string|null Текст ошибки
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * Сохранения ошибки
     *
     * @param  string  $error Текст ошибки
     */
    protected function setError(string $error): void
    {
        $this->error = $error;
    }
}
