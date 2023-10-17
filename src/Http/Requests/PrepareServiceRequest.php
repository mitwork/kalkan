<?php

namespace Mitwork\Kalkan\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Mitwork\Kalkan\Enums\ContentType;

class PrepareServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'auth' => 'array|nullable',
            'description' => 'string|nullable',
            'files' => 'array|required',
            'type' => [new Enum(ContentType::class)],
            'ttl' => 'numeric|nullable',
        ];
    }
}
