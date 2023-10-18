<?php

namespace Mitwork\Kalkan\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Mitwork\Kalkan\Rules\CacheValueExists;

class FetchRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['id' => $this->route('id')]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'id' => ['required', new CacheValueExists],
        ];
    }
}