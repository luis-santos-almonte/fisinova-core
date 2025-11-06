<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\ProcedureDetail;
use Illuminate\Support\Facades\DB;

class TherapyAppointmentService
{
    public function createTherapyAppointments(int $consultationAppointmentId, array $therapyData)
    {
        return DB::transaction(function () use ($consultationAppointmentId, $therapyData) {
            $consultation = Appointment::with('medicalRecord.procedure.procedureDetails')
                ->findOrFail($consultationAppointmentId);

            if (!$consultation->medicalRecord || !$consultation->medicalRecord->procedure) {
                throw new \Exception('La consulta no tiene procedimientos definidos');
            }

            $procedure = $consultation->medicalRecord->procedure;
            $createdAppointments = [];

            // Por cada procedure_detail, crear las sesiones necesarias
            foreach ($procedure->procedureDetails as $detail) {
                for ($i = 1; $i <= $detail->sessions_authorized; $i++) {
                    $appointment = Appointment::create([
                        'type' => 'therapy',
                        'consultation_appointment_id' => $consultationAppointmentId,
                        'procedure_detail_id' => $detail->id,
                        'session_number' => $i,
                        'total_sessions' => $detail->sessions_authorized,
                        'patient_id' => $consultation->patient_id,
                        'employee_id' => $therapyData['therapist_id'],
                        'appointment_date' => $therapyData['dates'][$i - 1] ?? null,
                        'start_time' => $therapyData['start_time'],
                        'end_time' => $therapyData['end_time'],
                        'status' => 'pendiente',
                        'insurance_id' => $consultation->insurance_id,
                        'payment_type' => $consultation->payment_type,
                        'authorization_id' => $consultation->authorization_id,
                    ]);

                    $createdAppointments[] = $appointment;
                }

                // Actualizar status del procedure_detail
                $detail->update(['status' => 'scheduled']);
            }

            return $createdAppointments;
        });
    }

    public function completeTherapySession(int $appointmentId, array $data)
    {
        return DB::transaction(function () use ($appointmentId, $data) {
            $appointment = Appointment::with('procedureDetail')->findOrFail($appointmentId);

            // 1. Actualizar appointment
            $appointment->update([
                'status' => 'completada',
                'actual_end_time' => now(),
                'notes' => $data['notes'] ?? null,
            ]);

            // 2. Incrementar sesiones completadas
            if ($appointment->procedureDetail) {
                $procedureDetail = $appointment->procedureDetail;
                $procedureDetail->increment('sessions_completed');

                // 3. Actualizar status según progreso
                if ($procedureDetail->sessions_completed >= $procedureDetail->sessions_authorized) {
                    $procedureDetail->update(['status' => 'completed']);
                } else {
                    $procedureDetail->update(['status' => 'in_progress']);
                }
            }

            return $appointment->fresh('procedureDetail');
        });
    }
}