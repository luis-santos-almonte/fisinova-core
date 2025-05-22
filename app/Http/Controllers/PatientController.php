<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterPatientRequest;
use App\Models\Patient;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;

class PatientController extends Controller
{

    public function index(FilterPatientRequest $request)
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

        return response()->json($query->paginate(50));
    }
    public function store(StorePatientRequest $request)
    {
        $patient = Patient::create($request->validated());

        return response()->json(
            $patient
        );
    }

    public function show(Patient $patient)
    {
        return response()->json($patient);
    }

    public function update(UpdatePatientRequest $request, Patient $patient)
    {
        $patient->update($request->validated());

        return response()->json(
            $patient
        );
    }
}
