<?php

namespace App\Http\Requests\Procedure;

use Illuminate\Foundation\Http\FormRequest;

class StoreProcedureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'appointment_id' => 'nullable|integer|exists:appointments,id',
            'patient_id' => 'required|integer|exists:patients,id',
            'employee_id' => 'required|integer|exists:employees,id',
            'procedure_type_id' => 'required|integer|exists:procedure_types,id',
            'insurance_code' => 'nullable|string|max:255',
            'insurance_id' => 'nullable|integer|exists:insurances,id',
            'case_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
            'authorization_code' => 'nullable|string|max:255',
            'dni' => 'nullable|string|max:20',
        ];
    }
}