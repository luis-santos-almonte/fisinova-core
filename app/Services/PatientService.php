<?php

namespace App\Services;

use App\Http\Requests\FilterPatientRequest;
use App\Models\Patient;

class PatientService
{
    public function getPatients(FilterPatientRequest $request)
    {
        $query = Patient::query();

        if ($request->has('active')) {
            $query->where('active', $request->active);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        if ($request->filled('name')) {
            $query->where(function ($q) use ($request) {
                $q->where('firstname', 'ILIKE', "%{$request->name}%")
                    ->orWhere('lastname', 'ILIKE', "%{$request->name}%");
            });
        }

        return $query->paginate(50);
    }
}
