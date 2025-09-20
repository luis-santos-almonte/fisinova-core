<?php
namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'active' => ['sometimes', Rule::in(['true', 'false', '1', '0'])],
            'search' => 'sometimes|string|min:2|max:255',
            'position_id' => 'sometimes|integer|exists:positions,id',
            'type' => ['sometimes', Rule::in(['medical', 'admin', 'all'])],
            'paginate' => 'sometimes|integer|min:1|max:100',
        ];
    }
}