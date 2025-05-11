<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;

class PatientController extends Controller
{
    public function index()
    {
        try {
            $patients = Patient::all();
            return response()->json($patients, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch patients'], 500);
        }
    }

    public function store(StorePatientRequest $request)
    {
        try {
            $patient = Patient::create($request->validated());

            return response()->json(['message' => 'Paciente creado!', 'data' => $patient], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create patient', 'error' => $e], 500);
        }
    }

    public function show(Patient $patient)
    {
        try {
            $patient = Patient::find($patient->id);
            return response()->json($patient, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch patient'], 500);
        }
    }

    public function update(UpdatePatientRequest $request, Patient $patient)
    {
        $patient->update($request->validated());
        return response()->json($patient, 200);
    }
}
