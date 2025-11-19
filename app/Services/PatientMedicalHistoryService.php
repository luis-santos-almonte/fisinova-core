<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\MedicalRecord;
use App\Models\TherapyRecord;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class PatientMedicalHistoryService
{
    // Datos de la empresa
    const COMPANY_NAME = 'Centro de Rehabilitación Física Fisinova';
    const COMPANY_RNC = '131-66268-4';
    const COMPANY_PHONE = '809-573-5555';
    const COMPANY_CITY = 'La Vega';

    /**
     * Generar historial médico en PDF
     */
    public function generateMedicalHistory(int $patientId, string $format = 'pdf', array $options = [])
    {
        try {
            $data = $this->getHistoryData($patientId, $options);

            if ($data['medical_records']->count() === 0 && $data['therapy_sessions']->count() === 0) {
                throw new \Exception('El paciente no tiene consultas ni sesiones de terapia registradas');
            }

            if ($format === 'pdf') {
                return $this->generatePDF($data);
            }

            throw new \Exception('Formato no soportado');
        } catch (\Exception $e) {
            Log::error('Error generando historial médico', [
                'error' => $e->getMessage(),
                'patient_id' => $patientId,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Obtener datos del historial médico usando MedicalRecord y TherapyRecord
     */
    public function getHistoryData(int $patientId, array $options = [])
    {
        try {
            // 1. Cargar paciente con seguro
            $patient = Patient::with(['insurance'])->findOrFail($patientId);

            // 2. Obtener CONSULTAS MÉDICAS con relaciones
            $medicalRecords = MedicalRecord::with([
                'appointment.employee',
                'procedure.procedureDetails.procedureStandard',
                'procedure.procedureDiagnostics.diagnostic'
            ])
                ->where('patient_id', $patientId)
                ->where('active', true)
                ->orderBy('created_at', 'desc')
                ->get();

            // 3. Obtener SESIONES DE TERAPIA
            $therapySessions = collect();
            if ($options['include_therapy_sessions'] ?? true) {
                $therapySessions = TherapyRecord::with([
                    'appointment',
                    'therapist'
                ])
                    ->where('patient_id', $patientId)
                    ->where('completed', true)
                    ->where('active', true)
                    ->orderBy('created_at', 'desc')
                    ->get();
            }

            // 4. Calcular estadísticas
            $stats = $this->calculateStats($medicalRecords, $therapySessions);

            // ✅ AGREGAR FLAG DE DATOS VACÍOS
            $hasData = $medicalRecords->count() > 0 || $therapySessions->count() > 0;

            return [
                'patient' => $patient,
                'medical_records' => $medicalRecords,
                'therapy_sessions' => $therapySessions,
                'stats' => $stats,
                'options' => $options,
                'has_data' => $hasData, // ✅ NUEVO
                'company' => [
                    'name' => self::COMPANY_NAME,
                    'rnc' => self::COMPANY_RNC,
                    'phone' => self::COMPANY_PHONE,
                    'city' => self::COMPANY_CITY,
                ],
                'generated_at' => now()->format('d/m/Y H:i'),
            ];
        } catch (\Exception $e) {
            Log::error('Error obteniendo datos del historial', [
                'error' => $e->getMessage(),
                'patient_id' => $patientId,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Calcular estadísticas del historial
     */
    protected function calculateStats($medicalRecords, $therapySessions)
    {
        // Contar diagnósticos totales (solo de consultas con procedure)
        $totalDiagnoses = 0;
        foreach ($medicalRecords as $record) {
            if ($record->procedure && $record->procedure->procedureDiagnostics) {
                $totalDiagnoses += $record->procedure->procedureDiagnostics->count();
            }
        }

        return [
            'total_consultations' => $medicalRecords->count(),
            'total_therapies' => $therapySessions->count(),
            'first_consultation' => $medicalRecords->last()?->created_at?->format('d/m/Y'),
            'last_consultation' => $medicalRecords->first()?->created_at?->format('d/m/Y'),
            'total_diagnoses' => $totalDiagnoses,
            'requires_therapy_count' => $medicalRecords->where('requires_therapy', true)->count(),
        ];
    }

    /**
     * Generar PDF
     */
    protected function generatePDF($data)
    {
        try {
            $pdf = Pdf::loadView('reports.patient-medical-history', $data);
            $pdf->setPaper('letter', 'portrait');

            $patient = $data['patient'];
            $filename = 'Historial_' . str_replace(' ', '_', $patient->firstname) . '_' .
                str_replace(' ', '_', $patient->lastname) . '_' . now()->format('Ymd') . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Error generando PDF del historial', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Error al generar el PDF: ' . $e->getMessage());
        }
    }
}
