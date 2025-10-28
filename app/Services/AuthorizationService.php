<?php

namespace App\Services;

use App\Models\Authorization;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;

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
            $appointment = Appointment::findOrFail($appointmentId);

            $appointment->update([
                'payment_type' => $data['payment_type'],
                'status' => 'confirmada',
                'confirmed_at' => now(),
                'confirmed_by' => $userId,
                'authorization_number' => $data['authorization_number'] ?? null,
            ]);

            if ($data['payment_type'] === 'insurance' && !empty($data['authorization_number'])) {
                $authData = [
                    'appointment_id' => $appointment->id,
                    'patient_id' => $appointment->patient_id,
                    'insurance_id' => $data['insurance_id'] ?? $appointment->insurance_id,
                    'created_by' => $userId,
                    'authorization_number' => $data['authorization_number'],
                    'authorization_date' => now()->toDateString(),
                    'authorization_type' => 'ambulatoria',
                    'notes' => $data['notes'] ?? null,
                    'active' => true,
                    'patient_name' => $appointment->patient->firstname,
                    'patient_last_name' => $appointment->patient->lastname,
                    'patient_dni' => $appointment->patient->dni,
                    'patient_insurance_code' => $appointment->patient->insurance_code,
                    'PSS_code' => $appointment->insurance->provider_code ?? null,
                    'medic_id' => $appointment->employee_id,
                    'medic_name' => $appointment->employee->firstname . ' ' . $appointment->employee->lastname,
                    'medic_specialty' => 'Fisiatra',
                    'city' => 'La Vega',
                    'stablishment_phone' => '809-123-4567',
                    'services_authorized' => $data['services_authorized'] ?? [],
                ];

                Authorization::create($authData);
            }

            return $appointment->load(['patient', 'employee', 'insurance', 'authorizations']);
        });
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
