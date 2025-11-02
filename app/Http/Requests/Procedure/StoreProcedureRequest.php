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
            'appointment_id' => 'required|integer|exists:appointments,id',
            'patient_id' => 'required|integer|exists:patients,id',
            'employee_id' => 'required|integer|exists:employees,id',
            'procedure_type_id' => 'required|integer|exists:procedure_types,id',
            'insurance_code' => 'nullable|string|max:255',
            'insurance_id' => 'nullable|integer|exists:insurances,id',
            'case_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
            'authorization_code' => 'nullable|string|max:255',
            'dni' => 'nullable|string|max:20',

            // NUEVOS CAMPOS
            'diagnosis_ids' => 'required|array|min:1',
            'diagnosis_ids.*' => 'integer|exists:diagnostic_standards,id',
            'procedure_ids' => 'required|array|min:1',
            'procedure_ids.*' => 'integer|exists:procedure_standards,id',
        ];
    }

    public function messages(): array
    {
        return [
            'diagnosis_ids.required' => 'Debe seleccionar al menos un diagnóstico',
            'diagnosis_ids.min' => 'Debe seleccionar al menos un diagnóstico',
            'procedure_ids.required' => 'Debe seleccionar al menos un procedimiento',
            'procedure_ids.min' => 'Debe seleccionar al menos un procedimiento',
        ];
    }
}
