<?php

namespace Mitwork\Kalkan;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Mitwork\Kalkan\Contracts\AbstractRequest;

/**
 * @property array<int, array<string, mixed>> $files
 * @property array<string, mixed> $auth
 * @property array<string, mixed> $organisation
 * @property string|null $description
 */
class CacheRequest implements AbstractRequest
{
    /** @var array<int, array<string, mixed>> */
    public array $files;

    /** @var array<string, mixed> */
    public array $auth;

    /** @var array<string, mixed> */
    public array $organisation;

    public ?string $description = null;

    /**
     * @param  array<int, array<string, mixed>>  $files  Документы
     * @param  array<string, mixed>  $auth  Параметры авторизации
     * @param  array<string, mixed>  $organisation  Данные организации
     */
    public function __construct(array $files, array $auth = [], array $organisation = [], ?string $description = null)
    {
        $this->files = $files;
        $this->auth = $auth;
        $this->organisation = $organisation;
        $this->description = $description;
    }

    /**
     * Правила валидации
     *
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'files' => 'array|required',
            'auth' => 'array',
            'organisation' => 'array|nullable',
            'description' => 'string|nullable',
        ];
    }

    /**
     * Валидация данных
     *
     * @param  string|array<int, string>|null  $fields  Проверяемые поля
     * @param  array<string, mixed>  $data  Данные вместо текущих
     *
     * @throws ValidationException
     */
    public function validate(string|array|null $fields = null, array $data = [], bool $throw = false): bool
    {
        $attributes = $this->toArray();

        if ($fields) {
            $attributes = collect($attributes)->only($fields)->all();
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
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'files' => $this->files,
            'auth' => $this->auth,
            'organisation' => $this->organisation,
            'description' => $this->description,
        ];
    }
}
