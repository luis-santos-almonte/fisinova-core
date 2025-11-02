<?php

namespace App\Http\Controllers;

use App\Models\MedicalRecord;
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
            'appointment_id' => 'required|integer|exists:appointments,id',
            'patient_id' => 'required|integer|exists:patients,id',
            'employee_id' => 'required|integer|exists:employees,id',
            'chief_complaint' => 'nullable|string',
            'current_illness' => 'nullable|string',
            'blood_pressure_systolic' => 'nullable|numeric',
            'blood_pressure_diastolic' => 'nullable|numeric',
            'heart_rate' => 'nullable|numeric',
            'temperature' => 'nullable|numeric',
            'respiratory_rate' => 'nullable|numeric',
            'weight' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'bmi' => 'nullable|numeric',
            'oxygen_saturation' => 'nullable|numeric',
            'smokes' => 'nullable|boolean',
            'smoking_frequency' => 'nullable|string',
            'drinks_alcohol' => 'nullable|boolean',
            'alcohol_frequency' => 'nullable|string',
            'uses_drugs' => 'nullable|boolean',
            'drug_type' => 'nullable|string',
            'has_diabetes' => 'nullable|boolean',
            'has_hypertension' => 'nullable|boolean',
            'has_asthma' => 'nullable|boolean',
            'other_conditions' => 'nullable|string',
            'previous_surgeries' => 'nullable|string',
            'current_medications' => 'nullable|string',
            'family_history' => 'nullable|string',
            'allergies' => 'nullable|string',
            'physical_exam' => 'nullable|string',
            'diagnosis_ids' => 'nullable|array|min:1',
            'diagnosis_ids.*' => 'integer|exists:diagnostic_standards,id',
            'diagnosis_notes' => 'nullable|string',
            'procedure_ids' => 'nullable|array|min:1',
            'procedure_ids.*' => 'integer|exists:procedure_standards,id',
            'procedure_notes' => 'nullable|string',
            'treatment_plan' => 'nullable|string',
            'prescriptions' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'general_notes' => 'nullable|string',
        ]);

        $record = $this->medicalRecordService->createMedicalRecord($validated);
        return $this->successResponse($record, 201);
    }

    public function update(Request $request, MedicalRecord $medicalRecord)
    {
        $validated = $request->validate([
            'chief_complaint' => 'sometimes|string',
            'current_illness' => 'sometimes|string',
            'blood_pressure_systolic' => 'sometimes|numeric',
            'blood_pressure_diastolic' => 'sometimes|numeric',
            'heart_rate' => 'sometimes|numeric',
            'temperature' => 'sometimes|numeric',
            'respiratory_rate' => 'sometimes|numeric',
            'weight' => 'sometimes|numeric',
            'height' => 'sometimes|numeric',
            'bmi' => 'sometimes|numeric',
            'oxygen_saturation' => 'sometimes|numeric',
            'smokes' => 'sometimes|boolean',
            'smoking_frequency' => 'sometimes|string',
            'drinks_alcohol' => 'sometimes|boolean',
            'alcohol_frequency' => 'sometimes|string',
            'uses_drugs' => 'sometimes|boolean',
            'drug_type' => 'sometimes|string',
            'has_diabetes' => 'sometimes|boolean',
            'has_hypertension' => 'sometimes|boolean',
            'has_asthma' => 'sometimes|boolean',
            'other_conditions' => 'sometimes|string',
            'previous_surgeries' => 'sometimes|string',
            'current_medications' => 'sometimes|string',
            'family_history' => 'sometimes|string',
            'allergies' => 'sometimes|string',
            'physical_exam' => 'sometimes|string',
            'diagnosis_ids' => 'sometimes|array|min:1',
            'diagnosis_ids.*' => 'integer|exists:diagnostic_standards,id',
            'diagnosis_notes' => 'sometimes|string',
            'procedure_ids' => 'sometimes|array|min:1',
            'procedure_ids.*' => 'integer|exists:procedure_standards,id',
            'procedure_notes' => 'sometimes|string',
            'treatment_plan' => 'sometimes|string',
            'prescriptions' => 'sometimes|string',
            'recommendations' => 'sometimes|string',
            'general_notes' => 'sometimes|string',
        ]);

        $record = $this->medicalRecordService->updateMedicalRecord($medicalRecord->id, $validated);
        return $this->successResponse($record);
    }

    public function getByAppointment($appointmentId)
    {
        $record = $this->medicalRecordService->getByAppointment($appointmentId);
        return $this->successResponse($record);
    }

    public function getPatientHistory($patientId)
    {
        $history = $this->medicalRecordService->getPatientHistory($patientId);
        return $this->successResponse($history);
    }
}
