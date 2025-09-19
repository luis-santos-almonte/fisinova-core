<?php

namespace App\Services;

use App\Models\Patient;

class PatientService
{
    public function getAllPatients(array $filters = [])
    {
        $query = Patient::query();

        // Apply filters
        if (isset($filters['active'])) {
            $active = $filters['active'] === 'true' || $filters['active'] === '1';
            $query->where('active', $active);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('firstname', 'ILIKE', "%{$filters['search']}%")
                    ->orWhere('lastname', 'ILIKE', "%{$filters['search']}%")
                    ->orWhere('dni', 'ILIKE', "%{$filters['search']}%")
                    ->orWhere('email', 'ILIKE', "%{$filters['search']}%");
            });
        }

        if (!empty($filters['city'])) {
            $query->where('city', 'ILIKE', "%{$filters['city']}%");
        }

        $pagination = $filters['paginate'] ?? 15;

        return $query->with(['insurance'])
            ->orderBy('created_at', 'desc')
            ->simplePaginate($pagination);
    }

    public function getPatientById($id)
    {
        return Patient::with(['insurance', 'appointments'])->findOrFail($id);
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
