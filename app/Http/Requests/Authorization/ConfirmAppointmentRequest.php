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
        ];

        $paymentType = $this->input('payment_type');
        $appointmentType = $this->route('appointment')?->type ?? $this->input('appointment_type');

        // CONSULTA + SEGURO: NO requiere autorización
        // TERAPIA + SEGURO: SÍ requiere autorización
        if ($paymentType === 'insurance' && $appointmentType === 'therapy') {
            $rules['authorization_number'] = 'required|string|max:255';
            $rules['insurance_id'] = 'required|integer|exists:insurances,id';
            $rules['authorization_date'] = 'nullable|date';
        } elseif ($paymentType === 'insurance') {
            // Consulta por seguro: seguro es requerido pero NO autorización
            $rules['insurance_id'] = 'required|integer|exists:insurances,id';
        }

        // RIESGO LABORAL: requiere número de caso (tanto para consulta como terapia)
        if ($paymentType === 'workplace_risk') {
            $rules['case_number'] = 'required|string|max:255';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'patient_id.exists' => 'El paciente seleccionado no existe',
            'authorization_number.required' => 'El número de autorización es requerido para terapias con seguro',
            'insurance_id.required' => 'Debe seleccionar un seguro médico',
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
            if ($appointmentType === 'therapy' && 
                $paymentType === 'insurance' && 
                !$this->input('authorization_number')) {
                $validator->errors()->add(
                    'authorization_number', 
                    'Las terapias por seguro requieren autorización previa'
                );
            }
        });
    }
}