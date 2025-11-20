<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use App\Services\ProcedureService;

class ConsultationService
{
    protected $procedureService;

    public function __construct(ProcedureService $procedureService)
    {
        $this->procedureService = $procedureService;
    }

    public function getDashboardStats($userId)
    {
        $employee = Employee::where('user_id', $userId)->firstOrFail();

        $today = now()->toDateString();

        $pending = Appointment::where('employee_id', $employee->id)
            ->where('confirmed_at', $today)
            ->where('status', 'confirmada')
            ->count();

        $inProgress = Appointment::where('employee_id', $employee->id)
            ->where('confirmed_at', $today)
            ->where('status', 'en_atencion')
            ->count();

        $completedToday = Appointment::where('employee_id', $employee->id)
            ->where('confirmed_at', $today)
            ->where('status', 'completada')
            ->count();

        $totalToday = Appointment::where('employee_id', $employee->id)
            ->where('confirmed_at', $today)
            ->count();

        return [
            'pending' => $pending,
            'in_progress' => $inProgress,
            'completed_today' => $completedToday,
            'total_today' => $totalToday,
        ];
    }

    public function getMyAppointments($userId, $status = null)
    {
        $employee = Employee::where('user_id', $userId)->firstOrFail();

        $query = Appointment::where('employee_id', $employee->id)
            ->where('appointment_date', '>=', now()->toDateString());

        if ($status) {
            $query->where('status', $status);
        }

        return $query->with(['patient', 'insurance'])
            ->orderBy('appointment_date')
            ->orderBy('start_time')
            ->get();
    }

    public function startConsultation($appointmentId)
    {
        return DB::transaction(function () use ($appointmentId) {
            $appointment = Appointment::findOrFail($appointmentId);
            $appointment->status = 'en_atencion';
            $appointment->save();

            return $appointment->load(['patient', 'employee', 'insurance']);
        });
    }

    public function completeConsultation($appointmentId)
    {
        return DB::transaction(function () use ($appointmentId) {
            $appointment = Appointment::with(['medicalRecord', 'patient', 'insurance'])
                ->findOrFail($appointmentId);

            // 1. Verificar que exista un medical record
            if (!$appointment->medicalRecord) {
                throw new \Exception('No se puede finalizar la consulta sin un registro médico');
            }

            $medicalRecord = $appointment->medicalRecord;

            // 2. Si ya existe procedure, completar/actualizar sus datos
            if ($medicalRecord->procedure_id) {
                $procedure = $medicalRecord->procedure;

                // Completar campos que faltan
                $procedure->update([
                    'insurance_id' => $appointment->insurance_id,
                    'insurance_code' => $appointment->insurance?->code,
                    'dni' => $appointment->patient?->dni,
                    'notes' => $medicalRecord->general_notes,
                ]);
            }
            // 3. Si NO existe procedure, crearlo AHORA con todos los datos
            else {
                $diagnosisIds = $medicalRecord->diagnosis_ids ?? [];
                $procedureIds = $medicalRecord->procedure_ids ?? [];
                $sessionsPerProcedure = $medicalRecord->sessions_per_procedure ?? [];

                // Usar el ProcedureService existente para crear
                $procedure = $this->procedureService->createProcedure([
                    'appointment_id' => $appointment->id,
                    'patient_id' => $appointment->patient_id,
                    'employee_id' => $appointment->employee_id,
                    'insurance_id' => $appointment->insurance_id,
                    'insurance_code' => $appointment->insurance?->code,
                    'dni' => $appointment->patient?->dni,
                    'notes' => $medicalRecord->general_notes,
                    'active' => true,
                    'diagnosis_ids' => $diagnosisIds,
                    'procedure_ids' => $procedureIds,
                    'sessions_per_procedure' => $sessionsPerProcedure, // ✅ Se pasa al service
                ]);

                // Vincular al medical record
                $medicalRecord->update(['procedure_id' => $procedure->id]);
            }

            // 4. Actualizar estado del appointment
            $appointment->status = 'pendiente autorizacion';
            $appointment->save();

            return $appointment->load([
                'patient',
                'employee',
                'insurance',
                'medicalRecord.procedure.procedureDetails.procedureStandard',
                'medicalRecord.procedure.procedureDiagnostics.diagnostic'
            ]);
        });
    }
}
