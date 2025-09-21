<?php

namespace App\Services;

use App\Models\Insurance;

class InsuranceService
{
    public function getAllInsurance(array $filters = [])
    {
        $query = Insurance::query();

        if (isset($filters['active'])) {
            $active = $filters['active'] === 'true' || $filters['active'] === '1';
            $query->where('active', $active);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'ILIKE', "%{$filters['search']}%")
                    ->orWhere('provider_code', 'ILIKE', "%{$filters['search']}%");
            });
        }

        return $query->orderBy('name')
            ->get();
    }

    public function getInsuranceById($id)
    {
        return Insurance::findOrFail($id);
    }

    public function createInsurance(array $data)
    {
        return Insurance::create($data);
    }

    public function updateInsurance($id, array $data)
    {
        $insurance = Insurance::findOrFail($id);
        $insurance->update($data);
        return $insurance;
    }

    public function deleteInsurance($id)
    {
        $insurance = Insurance::findOrFail($id);
        $insurance->delete();
        return true;
    }
}
