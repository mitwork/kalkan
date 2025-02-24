<?php

namespace Mitwork\Kalkan;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Mitwork\Kalkan\Contracts\AbstractDocument;

/**
 * Документ
 *
 * @property string $name Название файла
 * @property string $data Содержимое
 * @property string $mime Тип
 * @property int $size Размер
 * @property array $meta Атрибуты
 */
class CacheDocument implements AbstractDocument
{
    public string $name;

    public string $data;

    public string $mime;

    public int $size = 0;

    public array $meta = [];

    /**
     * Создание нового экземпляра объекта
     */
    public function __construct(string $name, string $data, string $mime, int $size = 0, array $meta = [])
    {
        $this->name = $name;
        $this->data = $data;
        $this->mime = $mime;
        $this->size = $size;
        $this->meta = $meta;

        if ($this->size === 0) {
            $this->size = strlen($this->data);
        }
    }

    /**
     * Правила валидации
     *
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'name' => 'string|required',
            'data' => 'string|required',
            'mime' => 'string|required',
            'size' => 'numeric',
            'meta' => 'array',
        ];
    }

    /**
     * Валидация данных
     *
     * @throws ValidationException
     */
    public function validate(string|array|null $fields = null, array $data = [], bool $throw = false): bool
    {
        $attributes = $this->toArray();

        if ($fields) {
            $attributes = collect($attributes)->only($fields);
        }

        if (count($data) > 0) {
            $self = new self(...$data);
            $attributes = $self->toArray();
        }

        $validator = Validator::make($attributes, $this->rules());

        try {
            $validator->validate();
        } catch (ValidationException $exception) {

            if ($throw) {
                throw $exception;
            }

            return false;
        }

        return ! $validator->fails();
    }

    /**
     * @return array<string, array|int|string>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'data' => $this->data,
            'mime' => $this->mime,
            'size' => $this->size,
            'meta' => $this->meta,
        ];
    }
}
