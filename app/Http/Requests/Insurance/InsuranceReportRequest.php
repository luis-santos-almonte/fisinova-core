<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InsuranceReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'format' => 'required|in:pdf,excel',
            'is_idoppril' => 'nullable|boolean',
        ];

        // Si NO es IDOPPRIL, insurance_id es requerido
        if (!$this->input('is_idoppril')) {
            $rules['insurance_id'] = 'required|exists:insurances,id';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'insurance_id.required' => 'Debe seleccionar un seguro o IDOPPRIL',
            'insurance_id.exists' => 'El seguro seleccionado no existe',
            'start_date.required' => 'La fecha de inicio es requerida',
            'end_date.required' => 'La fecha de fin es requerida',
            'end_date.after_or_equal' => 'La fecha fin debe ser mayor o igual a la fecha inicio',
            'format.required' => 'Debe seleccionar un formato',
            'format.in' => 'El formato debe ser PDF o Excel',
        ];
    }

    /**
     * Validación personalizada
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Si es IDOPPRIL, no debe enviar insurance_id
            if ($this->input('is_idoppril') && $this->input('insurance_id')) {
                $validator->errors()->add(
                    'insurance_id',
                    'No puede seleccionar un seguro cuando genera reporte de IDOPPRIL'
                );
            }

            // Si NO es IDOPPRIL, debe enviar insurance_id
            if (!$this->input('is_idoppril') && !$this->input('insurance_id')) {
                $validator->errors()->add(
                    'insurance_id',
                    'Debe seleccionar un seguro o IDOPPRIL'
                );
            }
        });
    }
}