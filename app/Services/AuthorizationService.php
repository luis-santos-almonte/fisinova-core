<?php

namespace App\Services;

use App\Models\Authorization;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

    /**
     * Confirmar llegada del paciente a una cita
     * 
     * LÓGICA DE CONFIRMACIÓN:
     * - Consulta + Seguro: NO requiere autorización previa
     * - Consulta + Privada: NO requiere autorización
     * - Consulta + Riesgo Laboral: requiere case_number, NO requiere autorización previa
     * - Terapia + Seguro: SÍ requiere autorización previa
     * - Terapia + Privada: NO requiere autorización
     * - Terapia + Riesgo Laboral: requiere case_number, NO requiere autorización previa
     */
    public function confirmAppointment($appointmentId, array $data, $userId)
    {
        return DB::transaction(function () use ($appointmentId, $data, $userId) {
            $appointment = Appointment::with(['patient', 'employee', 'insurance'])
                ->findOrFail($appointmentId);

            // Preparar datos de actualización base
            $appointmentUpdate = [
                'payment_type' => $data['payment_type'],
                'status' => 'confirmada',
                'confirmed_at' => now(),
                'confirmed_by' => $userId,
            ];

            // Actualizar patient_id si se proporciona (paciente nuevo o cambiado)
            if (isset($data['patient_id'])) {
                $appointmentUpdate['patient_id'] = $data['patient_id'];
            }

            // Manejar según tipo de pago
            switch ($data['payment_type']) {
                case 'insurance':
                    // Por seguro: siempre requiere insurance_id
                    $appointmentUpdate['insurance_id'] = $data['insurance_id'];

                    // Si es TERAPIA + SEGURO: requiere autorización
                    if ($appointment->type === Appointment::TYPE_THERAPY) {
                        if (empty($data['authorization_number'])) {
                            throw new \Exception('Las terapias por seguro requieren autorización previa');
                        }
                        $appointmentUpdate['authorization_number'] = $data['authorization_number'];
                    }
                    // Si es CONSULTA + SEGURO: NO requiere autorización previa
                    break;

                case 'workplace_risk':
                    // Riesgo laboral: requiere case_number
                    if (empty($data['case_number'])) {
                        throw new \Exception('El número de caso es requerido para riesgo laboral');
                    }
                    $appointmentUpdate['case_number'] = $data['case_number'];
                    break;

                case 'private':
                    // Privada: no requiere datos adicionales
                    break;
            }

            // Actualizar la cita
            $appointment->update($appointmentUpdate);

            // Recargar con relaciones actualizadas
            $appointment->refresh();
            $appointment->load('patient', 'employee', 'insurance');

            // Verificar que haya paciente antes de crear autorización
            if (!$appointment->patient_id) {
                throw new \Exception('No se puede confirmar una cita sin un paciente asignado');
            }

            // CREAR AUTORIZACIÓN solo si:
            // - Es TERAPIA por SEGURO (ya tiene authorization_number)
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
                'authorization_created' => $appointment->type === Appointment::TYPE_THERAPY &&
                    $data['payment_type'] === 'insurance',
            ]);

            return $appointment->load(['patient', 'employee', 'insurance', 'authorizations']);
        });
    }

    /**
     * Crear registro de autorización
     */
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

            // Datos del paciente
            'patient_name' => $appointment->patient->firstname,
            'patient_last_name' => $appointment->patient->lastname,
            'patient_dni' => $appointment->patient->dni,
            'patient_insurance_code' => $appointment->patient->insurance_code,
            'patient_gender' => $appointment->patient->sex,

            // Datos del establecimiento
            'PSS_code' => $appointment->insurance->provider_code ?? null,
            'city' => 'La Vega',
            'stablishment_phone' => '809-123-4567',

            // Datos del médico/terapista
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
