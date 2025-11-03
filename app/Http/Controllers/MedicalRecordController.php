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
            'chief_complaint' => 'sometimes|nullable|string',
            'current_illness' => 'sometimes|nullable|string',
            'blood_pressure_systolic' => 'sometimes|nullable|numeric',
            'blood_pressure_diastolic' => 'sometimes|nullable|numeric',
            'heart_rate' => 'sometimes|nullable|numeric',
            'temperature' => 'sometimes|nullable|numeric',
            'respiratory_rate' => 'sometimes|nullable|numeric',
            'weight' => 'sometimes|nullable|numeric',
            'height' => 'sometimes|nullable|numeric',
            'bmi' => 'sometimes|nullable|numeric',
            'oxygen_saturation' => 'sometimes|nullable|numeric',
            'smokes' => 'sometimes|nullable|boolean',
            'smoking_frequency' => 'sometimes|nullable|string',
            'drinks_alcohol' => 'sometimes|nullable|boolean',
            'alcohol_frequency' => 'sometimes|nullable|string',
            'uses_drugs' => 'sometimes|nullable|boolean',
            'drug_type' => 'sometimes|nullable|string',
            'has_diabetes' => 'sometimes|nullable|boolean',
            'has_hypertension' => 'sometimes|nullable|boolean',
            'has_asthma' => 'sometimes|nullable|boolean',
            'other_conditions' => 'sometimes|nullable|string',
            'previous_surgeries' => 'sometimes|nullable|string',
            'current_medications' => 'sometimes|nullable|string',
            'family_history' => 'sometimes|nullable|string',
            'allergies' => 'sometimes|nullable|string',
            'physical_exam' => 'sometimes|nullable|string',
            'diagnosis_ids' => 'sometimes|nullable|array|min:1',
            'diagnosis_ids.*' => 'nullable|integer|exists:diagnostic_standards,id',
            'diagnosis_notes' => 'sometimes|nullable|string',
            'procedure_ids' => 'sometimes|nullable|array|min:1',
            'procedure_ids.*' => 'nullable|integer|exists:procedure_standards,id',
            'procedure_notes' => 'sometimes|nullable|string',
            'treatment_plan' => 'sometimes|nullable|string',
            'prescriptions' => 'sometimes|nullable|string',
            'recommendations' => 'sometimes|nullable|string',
            'general_notes' => 'sometimes|nullable|string',

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
