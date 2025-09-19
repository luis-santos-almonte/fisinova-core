<?php

namespace App\Http\Controllers;

use App\Http\Requests\Patient\IndexPatientRequest;
use App\Http\Requests\Patient\StorePatientRequest;
use App\Http\Requests\Patient\UpdatePatientRequest;
use App\Models\Patient;
use App\Services\PatientService;
use App\Traits\ApiResponse;
class PatientController extends Controller
{
    use ApiResponse;

    protected $patientService;

    public function __construct(PatientService $patientService)
    {
        $this->patientService = $patientService;
    }

    public function index(IndexPatientRequest $request)
    {
        $patients = $this->patientService->getAllPatients($request->validated());
        return $this->successResponse($patients);
    }

    public function store(StorePatientRequest $request)
    {
        $patient = $this->patientService->createPatient($request->validated());
        return $this->successResponse($patient, 201);
    }

    public function show(Patient $patient)
    {
        $patient = $this->patientService->getPatientById($patient->id);
        return $this->successResponse($patient);
    }

    public function update(UpdatePatientRequest $request, Patient $patient)
    {
        $patient = $this->patientService->updatePatient($patient->id, $request->validated());
        return $this->successResponse($patient);
    }

    public function destroy(Patient $patient)
    {
        $this->patientService->deletePatient($patient->id);
        return $this->successResponse(null, 204);
    }
}
