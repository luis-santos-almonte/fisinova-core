<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexPatientRequest;
use App\Models\Patient;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Services\PatientService;

class PatientController extends Controller
{
    protected $patientService;

    public function __construct(PatientService $patientService)
    {
        $this->patientService = $patientService;
    }

    public function index(IndexPatientRequest $request)
    {
        $patients = $this->patientService->getAllPatients($request->validated());
        return response()->json($patients);
    }

    public function store(StorePatientRequest $request)
    {
        $patient = $this->patientService->createPatient($request->validated());
        return response()->json($patient);
    }

    public function show(Patient $patient)
    {
        return response()->json($patient);
    }

    public function update(UpdatePatientRequest $request, Patient $patient)
    {
        $patient = $this->patientService->updatePatient($patient->id, $request->validated());
        return response()->json($patient);
    }
}
