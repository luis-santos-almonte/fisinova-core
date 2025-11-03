<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\TherapyRecord;
use App\Models\Authorization;
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
        $therapistId = $request->user()->id;
        $date = $request->query('date', now()->toDateString());
        
        $therapies = Appointment::with(['patient', 'employee', 'authorization', 'therapyRecord'])
            ->where('employee_id', $therapistId)
            ->where('type', Appointment::TYPE_THERAPY)
            ->whereDate('appointment_date', $date)
            ->orderBy('start_time')
            ->get();
            
        return $this->successResponse($therapies);
    }

    /**
     * Iniciar una sesión de terapia
     */
    public function startSession(Request $request, $appointmentId)
    {
        $validated = $request->validate([
            'initial_patient_state' => 'required|string',
            'initial_observations' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($appointmentId, $validated, $request) {
            $appointment = Appointment::with(['patient', 'authorization'])
                ->findOrFail($appointmentId);

            // Verificar que no exista ya un registro
            $existingRecord = TherapyRecord::where('appointment_id', $appointmentId)->first();
            if ($existingRecord && $existingRecord->isStarted()) {
                throw new \Exception('Esta sesión ya fue iniciada');
            }

            // Crear o actualizar el registro de terapia
            $therapyRecord = TherapyRecord::updateOrCreate(
                ['appointment_id' => $appointmentId],
                [
                    'patient_id' => $appointment->patient_id,
                    'therapist_id' => $appointment->employee_id,
                    'authorization_id' => $appointment->authorization_id,
                    'initial_patient_state' => $validated['initial_patient_state'],
                    'initial_observations' => $validated['initial_observations'] ?? null,
                    'started_at' => now(),
                    'active' => true,
                ]
            );

            // Actualizar estado de la cita
            $appointment->update(['status' => 'en_atencion']);

            return $this->successResponse([
                'appointment' => $appointment->fresh(['patient', 'authorization']),
                'therapy_record' => $therapyRecord,
            ]);
        });
    }

    /**
     * Completar una sesión de terapia
     */
    public function completeSession(Request $request, $appointmentId)
    {
        $validated = $request->validate([
            'procedure_ids' => 'nullable|array',
            'procedure_ids.*' => 'integer|exists:procedure_standards,id',
            'procedure_notes' => 'nullable|string',
            'final_patient_state' => 'required|string',
            'final_observations' => 'nullable|string',
            'next_session_recommendation' => 'nullable|string',
            'intensity' => 'nullable|in:low,moderate,high',
        ]);

        return DB::transaction(function () use ($appointmentId, $validated) {
            $appointment = Appointment::with(['authorization'])->findOrFail($appointmentId);
            
            $therapyRecord = TherapyRecord::where('appointment_id', $appointmentId)->firstOrFail();
            
            if (!$therapyRecord->isStarted()) {
                throw new \Exception('Debe iniciar la sesión primero');
            }

            if ($therapyRecord->isCompleted()) {
                throw new \Exception('Esta sesión ya fue completada');
            }

            // Calcular duración
            $duration = now()->diffInMinutes($therapyRecord->started_at);

            // Actualizar el registro de terapia
            $therapyRecord->update([
                'procedure_ids' => $validated['procedure_ids'] ?? [],
                'procedure_notes' => $validated['procedure_notes'] ?? null,
                'final_patient_state' => $validated['final_patient_state'],
                'final_observations' => $validated['final_observations'] ?? null,
                'next_session_recommendation' => $validated['next_session_recommendation'] ?? null,
                'intensity' => $validated['intensity'] ?? null,
                'ended_at' => now(),
                'duration_minutes' => $duration,
                'completed' => true,
            ]);

            // Actualizar estado de la cita
            $appointment->update(['status' => 'completada']);

            // Incrementar contador de sesiones completadas en la autorización
            if ($appointment->authorization) {
                $appointment->authorization->incrementCompletedSessions();
            }

            return $this->successResponse([
                'appointment' => $appointment->fresh(['patient', 'authorization']),
                'therapy_record' => $therapyRecord->fresh(),
            ]);
        });
    }

    /**
     * Obtener detalles de una sesión
     */
    public function getSession($appointmentId)
    {
        $appointment = Appointment::with(['patient', 'employee', 'authorization', 'therapyRecord'])
            ->findOrFail($appointmentId);
            
        return $this->successResponse($appointment);
    }
}
