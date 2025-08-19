<?php

namespace App\Services;

use App\Models\Patient;
use App\Validators\Patient\PatientFilterValidator;

class PatientService
{
    public function getAllPatients(array $filters = [])
    {
        $filters = PatientFilterValidator::validate($filters);

        $pagination = $filters['paginate'] ?? 10;
        $query = Patient::query();

        if (isset($filters['active'])) {
            $active = $filters['active'] === 'true';
            $query->active($active);
        }
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]);
        }
        if (!empty($filters['city'])) {
            $query->where('city', $filters['city']);
        }
        if (!empty($filters['name'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('firstname', 'ILIKE', "%{$filters['name']}%")
                    ->orWhere('lastname', 'ILIKE', "%{$filters['name']}%");
            });
        }

        return $query->simplePaginate($pagination);
    }

    public function getPatientById($id)
    {
        return Patient::findOrFail($id);
    }

    public function createPatient(array $data)
    {
        return Patient::create($data);
    }

    public function updatePatient($id, array $data)
    {
        $patient = Patient::findOrFail($id);
        $patient->update($data);
        return $patient;
    }

    public function deletePatient($id)
    {
        $patient = Patient::findOrFail($id);
        $patient->delete();
        return true;
    }
}
