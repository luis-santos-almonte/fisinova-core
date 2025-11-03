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
            'authorization_number' => 'required|string|max:255',
            'authorization_date' => 'nullable|date',
            'insurance_id' => 'required|integer|exists:insurances,id',
            'sessions_authorized' => 'required|integer|min:1|max:100',
            'notes' => 'nullable|string|max:2000',
            
            // ✅ NUEVO: Terapista asignado (opcional)
            'therapist_id' => 'nullable|integer|exists:employees,id',
            
            // ✅ NUEVO: Array de sesiones programadas
            'sessions' => 'required|array|min:1',
            'sessions.*.date' => 'required|date|after_or_equal:today',
            'sessions.*.startTime' => 'required|date_format:H:i:s',
            'sessions.*.endTime' => 'required|date_format:H:i:s|after:sessions.*.startTime',
        ];
    }

    public function messages(): array
    {
        return [
            'authorization_number.required' => 'El número de autorización es requerido',
            'insurance_id.required' => 'Debe seleccionar un seguro',
            'sessions_authorized.required' => 'Debe especificar el número de sesiones autorizadas',
            'sessions_authorized.min' => 'Debe autorizar al menos 1 sesión',
            'sessions_authorized.max' => 'No puede autorizar más de 100 sesiones',
            
            'therapist_id.exists' => 'El terapista seleccionado no existe',
            
            'sessions.required' => 'Debe proporcionar las sesiones programadas',
            'sessions.min' => 'Debe programar al menos una sesión',
            'sessions.*.date.required' => 'Cada sesión debe tener una fecha',
            'sessions.*.date.after_or_equal' => 'Las fechas de las sesiones no pueden ser en el pasado',
            'sessions.*.startTime.required' => 'Cada sesión debe tener una hora de inicio',
            'sessions.*.endTime.required' => 'Cada sesión debe tener una hora de fin',
            'sessions.*.endTime.after' => 'La hora de fin debe ser posterior a la hora de inicio',
        ];
    }

    /**
     * Validación personalizada adicional
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $sessionsAuthorized = $this->input('sessions_authorized');
            $sessions = $this->input('sessions', []);

            // Validar que el número de sesiones coincida
            if (count($sessions) !== $sessionsAuthorized) {
                $validator->errors()->add(
                    'sessions',
                    "Debe programar exactamente {$sessionsAuthorized} sesiones"
                );
            }

            // Validar que no haya fechas duplicadas en el mismo horario
            $scheduledSlots = [];
            foreach ($sessions as $index => $session) {
                $key = ($session['date'] ?? '') . '|' . ($session['startTime'] ?? '');
                if (isset($scheduledSlots[$key])) {
                    $validator->errors()->add(
                        "sessions.{$index}.date",
                        "Ya existe una sesión programada en esta fecha y hora"
                    );
                }
                $scheduledSlots[$key] = true;
            }
        });
    }
    
}
