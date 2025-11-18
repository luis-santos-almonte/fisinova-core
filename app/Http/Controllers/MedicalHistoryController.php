<?php

namespace App\Http\Controllers;

use App\Services\PatientMedicalHistoryService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class MedicalHistoryController extends Controller
{
    use ApiResponse;

    protected $medicalHistoryService;

    public function __construct(PatientMedicalHistoryService $medicalHistoryService)
    {
        $this->medicalHistoryService = $medicalHistoryService;
    }

    /**
     * Generar historial médico del paciente en PDF
     */
    public function generate(Request $request, $patientId)
    {
        try {
            $validated = $request->validate([
                'format' => 'nullable|in:pdf',
                'include_vital_signs' => 'nullable|boolean',
                'include_medical_history' => 'nullable|boolean',
                'include_prescriptions' => 'nullable|boolean',
                'include_therapy_sessions' => 'nullable|boolean',
            ]);

            $format = $validated['format'] ?? 'pdf';
            $options = [
                'include_vital_signs' => $validated['include_vital_signs'] ?? true,
                'include_medical_history' => $validated['include_medical_history'] ?? true,
                'include_prescriptions' => $validated['include_prescriptions'] ?? true,
                'include_therapy_sessions' => $validated['include_therapy_sessions'] ?? true,
            ];

            return $this->medicalHistoryService->generateMedicalHistory(
                $patientId,
                $format,
                $options
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al generar el historial médico: ',
                $e->getMessage(),
                500
            );
        }
    }

    /**
     * Obtener vista previa del historial médico
     */
    public function preview($patientId)
    {
        try {
            $data = $this->medicalHistoryService->getHistoryData($patientId);

            return $this->successResponse($data);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener vista previa: ',
                $e->getMessage(),
                500
            );
        }
    }
}
