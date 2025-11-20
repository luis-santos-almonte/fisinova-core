<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\TherapyRecord;
use App\Models\Procedure;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TherapyController extends Controller
{
    use ApiResponse;

    /**
     * Dashboard del terapista - Obtener mis terapias del día
     */
    public function getMyTherapies(Request $request)
    {
        $userId = $request->user()->employee->id ?? null;

        if (!$userId) {
            return $this->errorResponse('Usuario sin empleado asociado', 'NO_EMPLOYEE', 400);
        }

        $date = $request->query('date', now()->toDateString());

        // Obtener las terapias del día
        $therapies = Appointment::with([
            'patient',
            'employee',
            'therapyRecord',
            'procedureDetail.procedureStandard'
        ])
            ->where('employee_id', $userId)
            ->where('type', Appointment::TYPE_THERAPY)
            ->whereRaw("DATE(COALESCE(confirmed_at, appointment_date)) = ?", [$date])
            ->orderBy('start_time')
            ->get();

        // Para cada terapia, obtener el Procedure de la consulta
        $therapies->each(function ($therapy) {
            if ($therapy->consultation_appointment_id) {
                // Buscar el Procedure que pertenece a la consulta
                $procedure = Procedure::with([
                    'procedureDetails.procedureStandard',
                    'procedureDiagnostics.diagnostic'
                ])
                    ->where('appointment_id', $therapy->consultation_appointment_id)
                    ->first();

                // Agregar como atributo dinámico
                $therapy->consultation_procedure = $procedure;
            }
        });

        return $this->successResponse($therapies);
    }

    /**
     * Iniciar una sesión de terapia
     */
    public function startSession(Request $request, $appointmentId)
    {
        $validated = $request->validate([
            'initial_patient_state' => 'required|string|max:500',
            'initial_observations' => 'nullable|string|max:2000',
        ]);

        return DB::transaction(function () use ($appointmentId, $validated) {
            $appointment = Appointment::with('patient')->findOrFail($appointmentId);

            // Verificar que no esté ya iniciada
            $existingRecord = TherapyRecord::where('appointment_id', $appointmentId)->first();
            if ($existingRecord && $existingRecord->started_at) {
                return $this->errorResponse('Esta sesión ya fue iniciada', 'ALREADY_STARTED', 400);
            }

            // Crear registro de terapia
            $therapyRecord = TherapyRecord::create([
                'appointment_id' => $appointmentId,
                'patient_id' => $appointment->patient_id,
                'therapist_id' => $appointment->employee_id,
                'authorization_id' => $appointment->authorization_id,
                'initial_patient_state' => $validated['initial_patient_state'],
                'initial_observations' => $validated['initial_observations'] ?? null,
                'started_at' => now(),
                'active' => true,
            ]);

            // Actualizar estado de la cita
            $appointment->update([
                'status' => 'en_atencion',
                'actual_start_time' => now(),
            ]);

            return $this->successResponse([
                'appointment' => $appointment->fresh(['patient']),
                'therapy_record' => $therapyRecord,
            ], 200, 'Sesión iniciada exitosamente');
        });
    }

    /**
     * Completar una sesión de terapia
     */
    public function completeSession(Request $request, $appointmentId)
    {
        $validated = $request->validate([
            'final_patient_state' => 'required|string|max:500',
            'final_observations' => 'nullable|string|max:2000',
            'next_session_recommendation' => 'nullable|string|max:1000',
            'intensity' => 'nullable|in:low,moderate,high',
        ]);

        return DB::transaction(function () use ($appointmentId, $validated) {
            $appointment = Appointment::with('procedureDetail')->findOrFail($appointmentId);

            $therapyRecord = TherapyRecord::where('appointment_id', $appointmentId)->firstOrFail();

            if (!$therapyRecord->started_at) {
                return $this->errorResponse('Debe iniciar la sesión primero', 'NOT_STARTED', 400);
            }

            if ($therapyRecord->completed) {
                return $this->errorResponse('Esta sesión ya fue completada', 'ALREADY_COMPLETED', 400);
            }

            // Calcular duración en minutos (asegurar que sea entero positivo)
            $startedAt = \Carbon\Carbon::parse($therapyRecord->started_at);
            $endedAt = now();
            $duration = abs((int) $startedAt->diffInMinutes($endedAt));

            // Actualizar el registro de terapia
            $therapyRecord->update([
                'final_patient_state' => $validated['final_patient_state'],
                'final_observations' => $validated['final_observations'] ?? null,
                'next_session_recommendation' => $validated['next_session_recommendation'] ?? null,
                'intensity' => $validated['intensity'] ?? null,
                'ended_at' => $endedAt,
                'duration_minutes' => $duration,
                'completed' => true,
            ]);

            // Actualizar estado de la cita
            $appointment->update([
                'status' => 'completada',
                'actual_end_time' => $endedAt,
            ]);

            // Incrementar contador en procedure_detail
            if ($appointment->procedureDetail) {
                $appointment->procedureDetail->increment('sessions_completed');

                if ($appointment->procedureDetail->sessions_completed >= $appointment->procedureDetail->sessions_authorized) {
                    $appointment->procedureDetail->update(['status' => 'completed']);
                } else {
                    $appointment->procedureDetail->update(['status' => 'in_progress']);
                }
            }

            return $this->successResponse([
                'appointment' => $appointment->fresh(['patient']),
                'therapy_record' => $therapyRecord->fresh(),
            ], 200, 'Sesión completada exitosamente');
        });
    }

    /**
     * Obtener detalles de una sesión
     */
    public function getSession($appointmentId)
    {
        $appointment = Appointment::with([
            'patient',
            'employee',
            'therapyRecord',
            'procedureDetail.procedureStandard'
        ])->findOrFail($appointmentId);

        // Obtener el Procedure de la consulta
        if ($appointment->consultation_appointment_id) {
            $procedure = Procedure::with([
                'procedureDetails.procedureStandard',
                'procedureDiagnostics.diagnostic'
            ])
                ->where('appointment_id', $appointment->consultation_appointment_id)
                ->first();

            $appointment->consultation_procedure = $procedure;
        }

        return $this->successResponse($appointment);
    }
}
