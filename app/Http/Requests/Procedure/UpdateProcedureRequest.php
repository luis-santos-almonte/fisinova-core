<?php

namespace App\Http\Requests\Procedure;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProcedureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'appointment_id' => 'sometimes|integer|exists:appointments,id',
            'patient_id' => 'sometimes|integer|exists:patients,id',
            'employee_id' => 'sometimes|integer|exists:employees,id',
            'procedure_type_id' => 'sometimes|integer|exists:procedure_types,id',
            'insurance_code' => 'sometimes|string|max:255',
            'insurance_id' => 'sometimes|integer|exists:insurances,id',
            'case_number' => 'sometimes|string|max:255',
            'notes' => 'sometimes|string|max:2000',
            'authorization_code' => 'sometimes|string|max:255',
            'dni' => 'sometimes|string|max:20',
            
            // CAMPOS OPCIONALES PARA UPDATE
            'diagnosis_ids' => 'sometimes|array|min:1',
            'diagnosis_ids.*' => 'integer|exists:diagnostic_standards,id',
            'procedure_ids' => 'sometimes|array|min:1',
            'procedure_ids.*' => 'integer|exists:procedure_standards,id',
        ];
    }
}