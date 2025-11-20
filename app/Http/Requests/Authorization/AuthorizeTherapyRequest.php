<?php

namespace App\Http\Requests\Authorization;

use Illuminate\Foundation\Http\FormRequest;

class AuthorizeTherapyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'authorization_number' => 'nullable|string|max:255',
            'authorization_date' => 'nullable|date',
            'insurance_id' => 'nullable|integer|exists:insurances,id',
            'sessions_authorized' => 'required|integer|min:1|max:100',

            // ✅ NUEVO: Montos obligatorios
            'insurance_amount' => 'nullable|numeric|min:0',
            'patient_amount' => 'required|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',

            'notes' => 'nullable|string|max:2000',
            'therapist_id' => 'nullable|integer|exists:employees,id',

            'sessions' => 'required|array|min:1',
            'sessions.*.date' => 'required|date|after_or_equal:today',
            'sessions.*.startTime' => 'required|date_format:H:i:s',
            'sessions.*.endTime' => 'required|date_format:H:i:s|after:sessions.*.startTime',
        ];
    }

    public function messages(): array
    {
        return [
            'sessions_authorized.required' => 'Debe especificar el número de sesiones autorizadas',
            'patient_amount.required' => 'El monto del paciente es requerido',
            'sessions.required' => 'Debe proporcionar las sesiones programadas',
            'sessions.*.date.required' => 'Cada sesión debe tener una fecha',
            'sessions.*.date.after_or_equal' => 'Las fechas de las sesiones no pueden ser en el pasado',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Obtener el appointment para verificar payment_type
            $appointmentId = $this->route('id');
            $appointment = $appointmentId ? \App\Models\Appointment::find($appointmentId) : null;

            // Solo validar authorization_number e insurance_id si NO es privada
            if ($appointment && $appointment->payment_type !== 'private') {
                if (!$this->authorization_number) {
                    $validator->errors()->add(
                        'authorization_number',
                        'El número de autorización es requerido para consultas con seguro'
                    );
                }

                if (!$this->insurance_id) {
                    $validator->errors()->add(
                        'insurance_id',
                        'El seguro es requerido para consultas con seguro'
                    );
                }
            }
        });
    }

    protected function prepareForValidation()
    {
        // Calcular total automáticamente si no viene
        if (!$this->has('total_amount')) {
            $this->merge([
                'total_amount' => ($this->insurance_amount ?? 0) + ($this->patient_amount ?? 0)
            ]);
        }

        // ✅ NUEVO: Si es consulta privada, forzar valores por defecto
        $appointmentId = $this->route('id');
        $appointment = $appointmentId ? \App\Models\Appointment::find($appointmentId) : null;

        if ($appointment && $appointment->payment_type === 'private') {
            $this->merge([
                'authorization_number' => 'PRIVADA-' . now()->format('YmdHis'),
                'insurance_amount' => 0,
                'insurance_id' => null,
            ]);
        }
    }
}
