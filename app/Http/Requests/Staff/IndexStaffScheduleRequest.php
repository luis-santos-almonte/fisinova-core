<?php

namespace App\Http\Requests\Staff;
// namespace App\Http\Requests\StaffSchedule;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexStaffScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'staff_id' => 'sometimes|integer|exists:staff,id',
            'cubicle_id' => 'sometimes|integer|exists:cubicles,id',
            'status' => 'sometimes|string|in:active,cancelled,completed',
            'is_recurring' => ['sometimes', Rule::in(['true', 'false', '1', '0'])],
            'date' => 'sometimes|date',
            'paginate' => 'sometimes|integer|min:1|max:100',
        ];
    }
}