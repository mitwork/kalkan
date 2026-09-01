<?php

namespace Mitwork\Kalkan\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'auth' => ['array', 'nullable'],
            'description' => ['string', 'nullable'],
            'files' => ['array', 'required'],
            'organisation' => ['array', 'nullable'],
            'ttl' => ['numeric', 'nullable'],
        ];
    }
}
