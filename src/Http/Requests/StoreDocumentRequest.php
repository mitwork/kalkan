<?php

namespace Mitwork\Kalkan\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Mitwork\Kalkan\Enums\ContentType;

class StoreDocumentRequest extends FormRequest
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
            'id' => 'alpha_num:ascii',
            'name' => 'required|string',
            'content' => 'string|required',
            'description' => 'string|nullable',
            'type' => [new Enum(ContentType::class)],
            'meta' => 'array',
            'auth' => 'array',
        ];
    }
}
