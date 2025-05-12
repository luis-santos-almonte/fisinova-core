<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;

class PatientController extends Controller
{
    public function index()
    {
        return response()->json(Patient::all());
    }

    public function store(StorePatientRequest $request)
    {
        $patient = Patient::create($request->validated());

        if (!$patient) {
            throw new \Exception('Error al crear el paciente.');
        }

        return response()->json([
            'message' => 'Paciente creado!',
            'data' => $patient
        ]);
    }

    public function show(Patient $patient)
    {
        if (!$patient) {
            throw new \Exception('Paciente no encontrado.');
        }
        return response()->json($patient);
    }

    public function update(UpdatePatientRequest $request, Patient $patient)
    {
        $patient->update($request->validated());

        return response()->json([
            'message' => 'Paciente actualizado!',
            'data' => $patient
        ]);
    }
}
