<?php

namespace Mitwork\Kalkan\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'id' => ['alpha_num:ascii', 'nullable'],
            'name' => ['required', 'string'],
            'data' => ['string', 'required'],
            'mime' => ['string', 'required'],
            'size' => ['numeric'],
            'meta' => ['array'],
        ];
    }
}
