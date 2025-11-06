<?php

namespace App\Http\Controllers;

use App\Services\MedicalRecordService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class MedicalRecordController extends Controller
{
    use ApiResponse;

    protected $medicalRecordService;

    public function __construct(MedicalRecordService $medicalRecordService)
    {
        $this->medicalRecordService = $medicalRecordService;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'patient_id' => 'required|exists:patients,id',
            'employee_id' => 'required|exists:employees,id',
            'diagnosis_ids' => 'nullable|array',
            'diagnosis_ids.*' => 'exists:diagnostic_standards,id',
            'procedure_ids' => 'nullable|array',
            'procedure_ids.*' => 'exists:procedure_standards,id',
            'sessions_per_procedure' => 'nullable|array',
        ]);

        $record = $this->medicalRecordService->createMedicalRecord($validated);
        return $this->successResponse($record, 'Registro médico creado exitosamente', 201);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'diagnosis_ids' => 'nullable|array',
            'procedure_ids' => 'nullable|array',
            'sessions_per_procedure' => 'nullable|array',
        ]);

        $record = $this->medicalRecordService->updateMedicalRecord($id, $validated);
        return $this->successResponse($record, 'Registro médico actualizado exitosamente');
    }

    public function getByAppointment(int $appointmentId)
    {
        $record = $this->medicalRecordService->getByAppointment($appointmentId);
        return $this->successResponse($record);
    }

    public function getPatientHistory(int $patientId)
    {
        $history = $this->medicalRecordService->getPatientHistory($patientId);
        return $this->successResponse($history);
    }
}
