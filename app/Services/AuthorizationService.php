<?php

namespace App\Services;

use App\Models\Authorization;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AuthorizationService
{
    public function getAllAuthorizations(array $filters = [])
    {
        $query = Authorization::query();

        if (isset($filters['active'])) {
            $active = $filters['active'] === 'true' || $filters['active'] === '1';
            $query->where('active', $active);
        }

        if (!empty($filters['appointment_id'])) {
            $query->where('appointment_id', $filters['appointment_id']);
        }

        if (!empty($filters['patient_id'])) {
            $query->where('patient_id', $filters['patient_id']);
        }

        if (!empty($filters['insurance_id'])) {
            $query->where('insurance_id', $filters['insurance_id']);
        }

        if (!empty($filters['authorization_number'])) {
            $query->where('authorization_number', 'ILIKE', "%{$filters['authorization_number']}%");
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('authorization_date', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('authorization_date', '<=', $filters['to_date']);
        }

        $pagination = $filters['paginate'] ?? 15;

        return $query->with(['appointment', 'patient', 'insurance', 'createdBy'])
            ->orderBy('authorization_date', 'desc')
            ->simplePaginate($pagination);
    }

    public function getAuthorizationById($id)
    {
        return Authorization::with([
            'appointment',
            'patient',
            'insurance',
            'createdBy'
        ])->findOrFail($id);
    }

    public function createAuthorization(array $data)
    {
        return DB::transaction(function () use ($data) {
            $authorization = Authorization::create($data);

            if (isset($data['appointment_id'])) {
                $appointment = Appointment::findOrFail($data['appointment_id']);

                if (!$appointment->authorization_number) {
                    $appointment->authorization_number = $data['authorization_number'];
                    $appointment->save();
                }
            }

            return $authorization;
        });
    }

    public function confirmAppointment($appointmentId, array $data, $userId)
    {
        return DB::transaction(function () use ($appointmentId, $data, $userId) {
            $appointment = Appointment::with(['patient', 'employee', 'insurance'])
                ->findOrFail($appointmentId);

            $appointmentUpdate = [
                'payment_type' => $data['payment_type'],
                'status' => 'confirmada',
                'confirmed_at' => now(),
                'confirmed_by' => $userId,
            ];

            if (isset($data['patient_id'])) {
                $appointmentUpdate['patient_id'] = $data['patient_id'];
            }

            switch ($data['payment_type']) {
                case 'insurance':
                    $appointmentUpdate['insurance_id'] = $data['insurance_id'];

                    if ($appointment->type === Appointment::TYPE_THERAPY) {
                        if (empty($data['authorization_number'])) {
                            throw new \Exception('Las terapias por seguro requieren autorización previa');
                        }
                        $appointmentUpdate['authorization_number'] = $data['authorization_number'];
                    }
                    break;

                case 'workplace_risk':
                    if (empty($data['case_number'])) {
                        throw new \Exception('El número de caso es requerido para riesgo laboral');
                    }
                    $appointmentUpdate['case_number'] = $data['case_number'];
                    break;

                case 'private':
                    break;
            }

            $appointment->update($appointmentUpdate);
            $appointment->refresh();
            $appointment->load('patient', 'employee', 'insurance');

            if (!$appointment->patient_id) {
                throw new \Exception('No se puede confirmar una cita sin un paciente asignado');
            }

            if (
                $appointment->type === Appointment::TYPE_THERAPY &&
                $data['payment_type'] === 'insurance' &&
                !empty($data['authorization_number'])
            ) {
                $this->createAuthorizationRecord($appointment, $data, $userId);
            }

            Log::info('Cita confirmada', [
                'appointment_id' => $appointment->id,
                'type' => $appointment->type,
                'payment_type' => $appointment->payment_type,
                'patient_id' => $appointment->patient_id,
            ]);

            return $appointment->load(['patient', 'employee', 'insurance', 'authorizations']);
        });
    }

    public function authorizeTherapySessions($appointmentId, array $data, $userId)
    {
        return DB::transaction(function () use ($appointmentId, $data, $userId) {
            $appointment = Appointment::with(['patient', 'employee', 'insurance'])
                ->findOrFail($appointmentId);

            if ($appointment->type !== Appointment::TYPE_CONSULTATION) {
                throw new \Exception('Solo se pueden autorizar terapias desde consultas completadas');
            }

            if ($appointment->status !== 'completada') {
                throw new \Exception('La consulta debe estar completada');
            }

            $medicalRecord = MedicalRecord::where('appointment_id', $appointmentId)->first();
            
            if (!$medicalRecord || !$medicalRecord->requires_therapy) {
                throw new \Exception('Esta consulta no requiere terapias');
            }

            $sessionsAuthorized = $data['sessions_authorized'] ?? $medicalRecord->therapy_sessions_needed;

            if ($sessionsAuthorized <= 0) {
                throw new \Exception('Debe autorizar al menos una sesión');
            }

            // Crear autorización
            $authData = [
                'appointment_id' => $appointment->id,
                'patient_id' => $appointment->patient_id,
                'insurance_id' => $data['insurance_id'] ?? $appointment->insurance_id,
                'created_by' => $userId,
                'medic_id' => $appointment->employee_id,
                'authorization_number' => $data['authorization_number'],
                'authorization_date' => $data['authorization_date'] ?? now()->toDateString(),
                'authorization_type' => 'ambulatoria',
                'sessions_authorized' => $sessionsAuthorized,
                'sessions_completed' => 0,
                'notes' => $data['notes'] ?? $medicalRecord->therapy_reason,
                'active' => true,
                'patient_name' => $appointment->patient->firstname,
                'patient_last_name' => $appointment->patient->lastname,
                'patient_dni' => $appointment->patient->dni,
                'patient_insurance_code' => $appointment->patient->insurance_code,
                'patient_gender' => $appointment->patient->sex,
                'city' => 'La Vega',
                'PSS_code' => $appointment->insurance->provider_code ?? null,
                'stablishment_phone' => '809-123-4567',
                'medic_name' => $appointment->employee->firstname . ' ' . $appointment->employee->lastname,
                'medic_specialty' => 'Fisiatra',
                'diagnosis_codes' => $medicalRecord->diagnosis_ids ?? [],
            ];

            $authorization = Authorization::create($authData);

            // Generar citas de terapia
            $this->generateTherapyAppointments(
                $appointment,
                $authorization,
                $sessionsAuthorized,
                $data['start_date'] ?? null
            );

            Log::info('Terapias autorizadas', [
                'appointment_id' => $appointment->id,
                'authorization_id' => $authorization->id,
                'sessions' => $sessionsAuthorized,
            ]);

            return $authorization->load(['therapyAppointments']);
        });
    }

    private function generateTherapyAppointments(
        Appointment $consultationAppointment,
        Authorization $authorization,
        int $sessions,
        ?string $startDate = null
    ) {
        $startDate = $startDate ? Carbon::parse($startDate) : Carbon::now()->addDays(1);
        
        // Asegurar que comience en un día laboral (lunes a viernes)
        while ($startDate->isWeekend()) {
            $startDate->addDay();
        }

        $createdAppointments = [];

        for ($i = 1; $i <= $sessions; $i++) {
            // Crear cita programada
            $therapyAppointment = Appointment::create([
                'employee_id' => $consultationAppointment->employee_id,
                'patient_id' => $consultationAppointment->patient_id,
                'appointment_date' => $startDate->format('Y-m-d'),
                'start_time' => $consultationAppointment->start_time,
                'end_time' => $consultationAppointment->end_time,
                'type' => Appointment::TYPE_THERAPY,
                'payment_type' => $consultationAppointment->payment_type,
                'insurance_id' => $consultationAppointment->insurance_id,
                'authorization_id' => $authorization->id,
                'authorization_number' => $authorization->authorization_number,
                'session_number' => $i,
                'total_sessions' => $sessions,
                'status' => 'programada',
                'notes' => "Sesión $i de $sessions - Generada automáticamente",
                'active' => true,
            ]);

            $createdAppointments[] = $therapyAppointment;

            // Avanzar al siguiente día laboral
            do {
                $startDate->addDay();
            } while ($startDate->isWeekend());
        }

        Log::info('Citas de terapia generadas', [
            'authorization_id' => $authorization->id,
            'total_sessions' => $sessions,
            'appointments_created' => count($createdAppointments),
        ]);

        return $createdAppointments;
    }

    private function createAuthorizationRecord(Appointment $appointment, array $data, int $userId)
    {
        $authData = [
            'appointment_id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'insurance_id' => $data['insurance_id'] ?? $appointment->insurance_id,
            'created_by' => $userId,
            'authorization_number' => $data['authorization_number'],
            'authorization_date' => $data['authorization_date'] ?? now()->toDateString(),
            'authorization_type' => 'ambulatoria',
            'notes' => $data['notes'] ?? null,
            'active' => true,
            'patient_name' => $appointment->patient->firstname,
            'patient_last_name' => $appointment->patient->lastname,
            'patient_dni' => $appointment->patient->dni,
            'patient_insurance_code' => $appointment->patient->insurance_code,
            'patient_gender' => $appointment->patient->sex,
            'PSS_code' => $appointment->insurance->provider_code ?? null,
            'city' => 'La Vega',
            'stablishment_phone' => '809-123-4567',
            'medic_id' => $appointment->employee_id,
            'medic_name' => $appointment->employee->firstname . ' ' . $appointment->employee->lastname,
            'medic_specialty' => 'Fisiatra',
            'services_authorized' => $data['services_authorized'] ?? [],
        ];

        return Authorization::create($authData);
    }

    public function updateAuthorization($id, array $data)
    {
        $authorization = Authorization::findOrFail($id);
        $authorization->update($data);
        return $authorization;
    }

    public function deleteAuthorization($id)
    {
        $authorization = Authorization::findOrFail($id);
        $authorization->delete();
        return true;
    }
}