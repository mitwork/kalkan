<?php

namespace Mitwork\Kalkan;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Mitwork\Kalkan\Contracts\AbstractRequest;

/**
 * @property array $files
 * @property array $auth
 * @property array $organisation
 * @property string|null $description
 */
class CacheRequest implements AbstractRequest
{
    public array $files;

    public array $auth;

    public array $organisation;

    public ?string $description = null;

    public function __construct(array $files, array $auth = [], array $organisation = [], string $description = null)
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
     * @throws ValidationException
     */
    public function validate(string|array $fields = null, array $data = [], bool $throw = false): bool
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
     * @return array<string, string>
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
