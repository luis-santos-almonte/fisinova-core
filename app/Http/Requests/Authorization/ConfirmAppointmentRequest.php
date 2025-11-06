<?php

namespace App\Http\Requests\Authorization;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConfirmAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'patient_id' => 'nullable|integer|exists:patients,id',
            'payment_type' => 'required|in:insurance,private,workplace_risk',
            'notes' => 'nullable|string|max:2000',
            'authorization_number' => 'nullable|string|max:255',
            'insurance_id' => 'nullable|integer|exists:insurances,id',
            'insurance_code' => 'nullable|string|max:255',
            'case_number' => 'nullable|string|max:255',
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'patient_id.exists' => 'El paciente seleccionado no existe',
            'authorization_number.required' => 'El número de autorización es requerido para terapias con seguro',
            'insurance_id.required' => 'Debe seleccionar un seguro médico',
            'insurance_code.required' => 'El código de seguro es requerido para terapias con seguro',
            'case_number.required' => 'El número de caso es requerido para riesgo laboral',
            'payment_type.required' => 'Debe seleccionar un tipo de pago',
            'payment_type.in' => 'El tipo de pago seleccionado no es válido',
        ];
    }

    /**
     * Validación adicional después de las reglas básicas
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $paymentType = $this->input('payment_type');
            $appointmentType = $this->route('appointment')?->type ?? $this->input('appointment_type');

            // Validar que terapia por seguro siempre tenga autorización
            if (
                $appointmentType === 'therapy' &&
                $paymentType === 'insurance' &&
                !$this->input('authorization_number')
            ) {
                $validator->errors()->add(
                    'authorization_number',
                    'Las terapias por seguro requieren autorización previa'
                );
            }
        });
    }
}
