<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_active' => ['sometimes', Rule::in(['true', 'false', '1', '0'])],
            'search' => 'sometimes|string|min:2|max:255',
            'position_id' => 'sometimes|integer|exists:positions,id',
            'paginate' => 'sometimes|integer|min:1|max:100',
        ];
    }
}