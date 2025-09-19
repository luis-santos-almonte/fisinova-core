<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexPatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ✅ FIXED
    }

    public function rules(): array
    {
        return [
            'active' => ['sometimes', Rule::in(['true', 'false', '1', '0'])],
            'search' => 'sometimes|string|min:2|max:255',
            'city' => 'sometimes|string|max:255',
            'paginate' => 'sometimes|integer|min:1|max:100',
        ];
    }
}