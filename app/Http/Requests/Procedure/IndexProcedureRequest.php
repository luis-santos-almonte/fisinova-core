<?php

namespace App\Http\Requests\Procedure;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexProcedureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'active' => ['sometimes', Rule::in(['true', 'false', '1', '0'])],
            'patient_id' => 'sometimes|integer|exists:patients,id',
            'employee_id' => 'sometimes|integer|exists:employees,id',
            'appointment_id' => 'sometimes|integer|exists:appointments,id',
            'procedure_type_id' => 'sometimes|integer|exists:procedure_types,id',
            'paginate' => 'sometimes|integer|min:1|max:100',
        ];
    }
}