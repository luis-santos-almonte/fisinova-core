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
                    $appointmentUpdate['insurance_code'] = $data['insurance_code'];

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

    private function createAuthorizationRecord(Appointment $appointment, array $data, int $userId)
    {
        $consultationAppointment = null;
        if ($appointment->consultation_appointment_id) {
            $consultationAppointment = Appointment::with('employee')
                ->find($appointment->consultation_appointment_id);
        }

        $authData = [
            'appointment_id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'insurance_id' => $data['insurance_id'] ?? $appointment->insurance_id,
            'created_by' => $userId,
            'authorization_number' => $data['authorization_number'],
            'authorization_date' => $data['authorization_date'] ?? now()->toDateString(),
            'authorization_type' => 'ambulatoria',

            // Determinar payment_type según los datos
            'payment_type' => $this->determinePaymentType($data, $appointment),

            // Montos
            'insurance_amount' => $data['insurance_amount'] ?? 0,
            'patient_amount' => $data['patient_amount'] ?? 0,
            'total_amount' => ($data['insurance_amount'] ?? 0) + ($data['patient_amount'] ?? 0),

            'notes' => $data['notes'] ?? null,
            'active' => true,

            // Datos del paciente
            'patient_name' => $appointment->patient->firstname,
            'patient_last_name' => $appointment->patient->lastname,
            'patient_dni' => $appointment->patient->dni,
            'patient_insurance_code' => $appointment->patient->insurance_code,
            'patient_gender' => $appointment->patient->sex,

            // Datos del establecimiento
            'PSS_code' => $appointment->insurance->provider_code ?? null,
            'city' => 'La Vega',
            'stablishment_phone' => '809-573-5555',

            // Datos del médico
            'medic_id' => $consultationAppointment ? $consultationAppointment->employee_id : $appointment->employee_id,
            'medic_name' => $consultationAppointment
                ? ($consultationAppointment->employee->firstname . ' ' . $consultationAppointment->employee->lastname)
                : ($appointment->employee->firstname . ' ' . $appointment->employee->lastname),
            'medic_specialty' => 'Fisiatra',

            'services_authorized' => $data['services_authorized'] ?? [],
        ];

        // Agregar case_number si es riesgo laboral
        if (isset($data['case_number'])) {
            $authData['case_number'] = $data['case_number'];
        }

        return Authorization::create($authData);
    }

    /**
     * Determinar el tipo de pago basado en los datos
     */
    private function determinePaymentType(array $data, Appointment $appointment): string
    {
        // Si tiene case_number es riesgo laboral
        if (isset($data['case_number']) && !empty($data['case_number'])) {
            return 'workplace_risk';
        }

        // Si el appointment tiene payment_type, usar ese
        if ($appointment->payment_type) {
            return $appointment->payment_type;
        }

        // Si tiene insurance_id es seguro
        if (isset($data['insurance_id']) || $appointment->insurance_id) {
            return 'insurance';
        }

        // Por defecto privado
        return 'private';
    }

    /**
     * Autorizar sesiones de terapia desde una consulta completada
     * 
     * @param int $appointmentId
     * @param array $data Debe incluir:
     *   - authorization_number: string
     *   - insurance_id: int
     *   - sessions_authorized: int
     *   - therapist_id: int (opcional, ID del terapista a asignar)
     *   - sessions: array de objetos con date, startTime, endTime
     * @param int $userId
     * @return Authorization
     */
    public function authorizeTherapySessions($appointmentId, array $data, $userId)
    {
        return DB::transaction(function () use ($appointmentId, $data, $userId) {
            $appointment = Appointment::with(['patient', 'employee', 'insurance'])
                ->findOrFail($appointmentId);

            if ($appointment->type !== Appointment::TYPE_CONSULTATION) {
                throw new \Exception('Solo se pueden autorizar terapias desde consultas completadas');
            }

            if ($appointment->status !== 'pendiente autorizacion') {
                throw new \Exception('La consulta debe estar pendiente de autorización para proceder');
            }

            $medicalRecord = MedicalRecord::where('appointment_id', $appointmentId)->first();

            if (!$medicalRecord || !$medicalRecord->requires_therapy) {
                throw new \Exception('Esta consulta no requiere terapias');
            }

            $sessionsAuthorized = $data['sessions_authorized'] ?? $medicalRecord->therapy_sessions_needed;

            if ($sessionsAuthorized <= 0) {
                throw new \Exception('Debe autorizar al menos una sesión');
            }

            // Validar que se hayan proporcionado las sesiones programadas
            if (empty($data['sessions']) || !is_array($data['sessions'])) {
                throw new \Exception('Debe proporcionar las sesiones programadas');
            }

            if (count($data['sessions']) !== $sessionsAuthorized) {
                throw new \Exception('El número de sesiones programadas no coincide con las sesiones autorizadas');
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

                // ✅ AGREGAR: Montos
                'insurance_amount' => $data['insurance_amount'] ?? 0,
                'patient_amount' => $data['patient_amount'] ?? 0,
                'total_amount' => ($data['insurance_amount'] ?? 0) + ($data['patient_amount'] ?? 0),

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
                'stablishment_phone' => '809-573-5555',
                'medic_name' => $appointment->employee->firstname . ' ' . $appointment->employee->lastname,
                'medic_specialty' => 'Fisiatra',
                'diagnosis_codes' => $medicalRecord->diagnosis_ids ?? [],
            ];

            $authorization = Authorization::create($authData);

            // Generar citas de terapia con horarios específicos
            $this->generateTherapyAppointmentsWithSchedule(
                $appointment,
                $authorization,
                $data['sessions'],
                $data['therapist_id'] ?? null
            );

            $appointment->status = 'completada';
            $appointment->save();

            return $authorization->load(['therapyAppointments']);
        });
    }

    /**
     * Generar citas de terapia con horarios personalizados
     * 
     * @param Appointment $consultationAppointment
     * @param Authorization $authorization
     * @param array $sessions Array de objetos: [{date, startTime, endTime}]
     * @param int|null $therapistId ID del terapista a asignar (null = mismo médico)
     * @return array
     */
    private function generateTherapyAppointmentsWithSchedule(
        Appointment $consultationAppointment,
        Authorization $authorization,
        array $sessions,
        ?int $therapistId = null
    ) {
        $createdAppointments = [];
        $sessionNumber = 1;
        $totalSessions = count($sessions);

        // Si no se especifica terapista, usar el mismo médico de la consulta
        $assignedTherapistId = $therapistId ?? $consultationAppointment->employee_id;

        foreach ($sessions as $session) {

            // Parsear fecha y horas
            $appointmentDate = Carbon::parse($session['date'])->format('Y-m-d');
            $startTime = Carbon::parse($session['startTime'])->format('H:i:s');
            $endTime = Carbon::parse($session['endTime'])->format('H:i:s');

            // Crear cita de terapia
            $therapyAppointment = Appointment::create([
                'employee_id' => $assignedTherapistId, // Médico referente
                'patient_id' => $consultationAppointment->patient_id,
                'appointment_date' => $appointmentDate,
                'start_time' => $startTime, // Hora programada
                'end_time' => $endTime, // Hora programada
                'actual_start_time' => null, // Se llenará cuando inicie la sesión
                'actual_end_time' => null, // Se llenará cuando termine la sesión
                'type' => Appointment::TYPE_THERAPY,
                'payment_type' => $consultationAppointment->payment_type,
                'insurance_id' => $consultationAppointment->insurance_id,
                'authorization_id' => $authorization->id,
                'authorization_number' => $authorization->authorization_number,
                'session_number' => $sessionNumber,
                'total_sessions' => $totalSessions,
                'status' => 'programada',
                'notes' => "Sesión $sessionNumber de $totalSessions - Terapia autorizada",
                'active' => true,
                'consultation_appointment_id' => $consultationAppointment->id,
            ]);

            $createdAppointments[] = $therapyAppointment;
            $sessionNumber++;
        }

        return $createdAppointments;
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
