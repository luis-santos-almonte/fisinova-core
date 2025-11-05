<?php

namespace App\Http\Requests\Insurance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexInsuranceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'active' => ['sometimes', Rule::in(['true', 'false', '1', '0'])],
            'name' => 'sometimes|string|min:2|max:255',
            'provider_code' => 'sometimes|string|min:2|max:255',
        ];
    }
}