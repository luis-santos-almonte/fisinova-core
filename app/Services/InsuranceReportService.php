<?php

namespace App\Services;

use App\Models\Authorization;
use App\Models\Insurance;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InsuranceReportExport;
use Carbon\Carbon;

class InsuranceReportService
{
    // Datos de la empresa
    const COMPANY_NAME = 'Centro de Rehabilitación Física Fisinova';
    const COMPANY_RNC = '131-66268-4';
    const COMPANY_PHONE = '809-573-5555';
    const COMPANY_CITY = 'La Vega';

    /**
     * Generar reporte en el formato solicitado (PDF o Excel)
     */
    public function generateReport(array $filters, string $format = 'pdf')
    {
        try {
            $reportData = $this->getReportData($filters);

            if ($format === 'pdf') {
                return $this->generatePDF($reportData);
            }

            return $this->generateExcel($reportData);
        } catch (\Exception $e) {
            Log::error('Error generando reporte', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'filters' => $filters
            ]);
            throw $e;
        }
    }

    /**
     * Obtener vista previa de datos
     */
    public function getPreviewData(array $filters)
    {
        try {
            $reportData = $this->getReportData($filters);
            
            // Convertir Collection a array para el frontend
            if (isset($reportData['services'])) {
                $reportData['services'] = $reportData['services']->toArray();
            }
            
            return $reportData;
        } catch (\Exception $e) {
            Log::error('Error en getPreviewData', [
                'error' => $e->getMessage(),
                'filters' => $filters
            ]);
            throw $e;
        }
    }

    /**
     * Obtener datos del reporte ORGANIZADOS POR PACIENTE
     */
    protected function getReportData(array $filters)
    {
        try {
            $isIdoppril = isset($filters['is_idoppril']) && $filters['is_idoppril'];

            Log::info('Getting report data', [
                'is_idoppril' => $isIdoppril,
                'insurance_id' => $filters['insurance_id'] ?? null,
                'dates' => [$filters['start_date'], $filters['end_date']]
            ]);

            // Si es IDOPPRIL
            if ($isIdoppril) {
                $authorizations = $this->getIdopprilAuthorizations($filters);
                $insurance = (object)[
                    'id' => 0,
                    'name' => 'IDOPPRIL',
                    'code' => 'IDOPPRIL',
                    'provider_code' => 'IDOP001',
                ];
            } else {
                // Buscar por insurance_id normal
                if (empty($filters['insurance_id'])) {
                    throw new \Exception('insurance_id es requerido cuando no es IDOPPRIL');
                }
                
                $insurance = Insurance::findOrFail($filters['insurance_id']);
                $authorizations = $this->getInsuranceAuthorizations($filters);
            }

            Log::info('Authorizations retrieved', [
                'count' => $authorizations->count()
            ]);

            // Agrupar por paciente y ordenar servicios
            $groupedByPatient = $this->groupAndSortByPatient($authorizations);

            // Calcular totales (asegurar que sean números)
            $summary = [
                'total_services' => $authorizations->count(),
                'total_insurance_amount' => (float) ($authorizations->sum('insurance_amount') ?? 0),
                'total_patient_amount' => (float) ($authorizations->sum('patient_amount') ?? 0),
                'total_amount' => (float) ($authorizations->sum('total_amount') ?? 0),
                'consultations_count' => $authorizations->where('service_type', 'consultation')->count(),
                'therapies_count' => $authorizations->where('service_type', 'therapy')->count(),
                'admissions_count' => $authorizations->where('service_type', 'admission')->count(),
            ];

            Log::info('Report summary', $summary);

            return [
                'services' => $groupedByPatient,
                'summary' => $summary,
                'insurance' => $insurance,
                'is_workplace_risk' => $isIdoppril,
                'is_idoppril' => $isIdoppril,
                'period' => [
                    'start' => $filters['start_date'],
                    'end' => $filters['end_date'],
                ],
                'company' => [
                    'name' => self::COMPANY_NAME,
                    'rnc' => self::COMPANY_RNC,
                    'phone' => self::COMPANY_PHONE,
                    'city' => self::COMPANY_CITY,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Error in getReportData', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Obtener autorizaciones de IDOPPRIL (riesgo laboral)
     */
    protected function getIdopprilAuthorizations(array $filters)
    {
        return Authorization::whereBetween('authorization_date', [
                $filters['start_date'],
                $filters['end_date']
            ])
            ->where('active', true)
            ->whereNotNull('case_number')
            ->with(['patient', 'appointment'])
            ->get()
            ->map(function ($auth) {
                return (object)[
                    'id' => $auth->id,
                    'authorization_date' => $auth->authorization_date,
                    'authorization_number' => $auth->authorization_number,
                    'insurance_amount' => (float) ($auth->insurance_amount ?? 0),
                    'patient_amount' => (float) ($auth->patient_amount ?? 0),
                    'total_amount' => (float) ($auth->total_amount ?? 0),
                    'patient_name' => $auth->patient_name ?? $auth->patient?->firstname ?? 'N/A',
                    'patient_last_name' => $auth->patient_last_name ?? $auth->patient?->lastname ?? '',
                    'case_number' => $auth->case_number,
                    'patient_id' => $auth->patient_id,
                    'service_type' => $auth->appointment?->type ?? 'consultation',
                    'procedure_description' => $this->getProcedureDescription($auth->appointment?->type ?? 'consultation'),
                ];
            });
    }

    /**
     * Obtener autorizaciones de un seguro normal
     */
    protected function getInsuranceAuthorizations(array $filters)
    {
        return Authorization::where('insurance_id', $filters['insurance_id'])
            ->whereBetween('authorization_date', [
                $filters['start_date'],
                $filters['end_date']
            ])
            ->where('active', true)
            ->with(['patient', 'appointment'])
            ->get()
            ->map(function ($auth) {
                return (object)[
                    'id' => $auth->id,
                    'authorization_date' => $auth->authorization_date,
                    'authorization_number' => $auth->authorization_number,
                    'insurance_amount' => (float) ($auth->insurance_amount ?? 0),
                    'patient_amount' => (float) ($auth->patient_amount ?? 0),
                    'total_amount' => (float) ($auth->total_amount ?? 0),
                    'patient_name' => $auth->patient_name ?? $auth->patient?->firstname ?? 'N/A',
                    'patient_last_name' => $auth->patient_last_name ?? $auth->patient?->lastname ?? '',
                    'patient_insurance_code' => $auth->patient_insurance_code ?? $auth->patient?->insurance_code ?? 'N/A',
                    'patient_id' => $auth->patient_id,
                    'service_type' => $auth->appointment?->type ?? 'consultation',
                    'procedure_description' => $this->getProcedureDescription($auth->appointment?->type ?? 'consultation'),
                ];
            });
    }

    /**
     * Obtener descripción del procedimiento según el tipo
     */
    protected function getProcedureDescription($type)
    {
        $descriptions = [
            'consultation' => 'CONSULTA',
            'therapy' => 'TERAPIA',
            'admission' => 'INTERNAMIENTO',
        ];

        return $descriptions[$type] ?? 'CONSULTA';
    }

    /**
     * Agrupar por paciente y ordenar:
     * Prioridad: Consulta -> Internamiento -> Terapia
     */
    protected function groupAndSortByPatient($authorizations)
    {
        $grouped = $authorizations->groupBy('patient_id')->map(function ($patientAuths) {
            return $patientAuths->sortBy(function ($auth) {
                $priority = [
                    'consultation' => 1,
                    'admission' => 2,
                    'therapy' => 3,
                ];
                return $priority[$auth->service_type] ?? 4;
            })->values();
        });

        // Aplanar manteniendo el orden
        $flattened = collect();
        foreach ($grouped as $patientServices) {
            foreach ($patientServices as $service) {
                $flattened->push($service);
            }
        }

        return $flattened;
    }

    /**
     * Generar PDF
     */
    protected function generatePDF($reportData)
    {
        try {
            $pdf = Pdf::loadView('reports.insurance-report', $reportData);
            $pdf->setPaper('letter', 'portrait');
            
            $filename = $this->generateFilename($reportData, 'pdf');
            
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Error generando PDF', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Error al generar el PDF: ' . $e->getMessage());
        }
    }

    /**
     * Generar Excel
     */
    protected function generateExcel($reportData)
    {
        try {
            $filename = $this->generateFilename($reportData, 'xlsx');
            
            return Excel::download(
                new InsuranceReportExport($reportData),
                $filename
            );
        } catch (\Exception $e) {
            Log::error('Error generando Excel', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Error al generar el Excel: ' . $e->getMessage());
        }
    }

    /**
     * Generar nombre de archivo
     */
    protected function generateFilename($reportData, $extension)
    {
        $insurance = $reportData['insurance'];
        $insuranceName = str_replace([' ', '/'], '_', $insurance->name);
        $date = now()->format('Ymd_His');

        return "Reporte_{$insuranceName}_{$date}.{$extension}";
    }

    /**
     * Obtener estadísticas para reportería
     */
    public function getReportStats($startDate, $endDate)
    {
        $stats = Authorization::whereBetween('authorization_date', [$startDate, $endDate])
            ->where('active', true)
            ->select([
                DB::raw('COUNT(*) as total_services'),
                DB::raw('COUNT(DISTINCT patient_id) as total_patients'),
                DB::raw('COALESCE(SUM(insurance_amount), 0) as total_insurance_amount'),
                DB::raw('COALESCE(SUM(patient_amount), 0) as total_patient_amount'),
                DB::raw('COALESCE(SUM(total_amount), 0) as total_amount'),
            ])
            ->first();

        return [
            'current_period_amount' => '$' . number_format($stats->total_amount ?? 0, 2),
            'services_performed' => $stats->total_services ?? 0,
            'patients_attended' => $stats->total_patients ?? 0,
            'insurance_amount' => '$' . number_format($stats->total_insurance_amount ?? 0, 2),
            'patient_amount' => '$' . number_format($stats->total_patient_amount ?? 0, 2),
        ];
    }

    /**
     * Obtener estadísticas por seguro + IDOPPRIL
     */
    public function getStatsByInsurance($startDate, $endDate)
    {
        // Estadísticas de seguros normales
        $insuranceStats = Authorization::with('insurance')
            ->whereNotNull('insurance_id')
            ->whereBetween('authorization_date', [$startDate, $endDate])
            ->where('active', true)
            ->select([
                'insurance_id',
                DB::raw('COUNT(*) as total_services'),
                DB::raw('COALESCE(SUM(total_amount), 0) as total_amount'),
            ])
            ->groupBy('insurance_id')
            ->get()
            ->map(function ($stat) {
                return [
                    'insurance_name' => $stat->insurance->name ?? 'Desconocido',
                    'total_services' => $stat->total_services,
                    'total_amount' => '$' . number_format($stat->total_amount, 2),
                ];
            });

        // Estadísticas de IDOPPRIL (riesgo laboral)
        $idopprilStats = Authorization::whereNotNull('case_number')
            ->whereBetween('authorization_date', [$startDate, $endDate])
            ->where('active', true)
            ->select([
                DB::raw('COUNT(*) as total_services'),
                DB::raw('COALESCE(SUM(total_amount), 0) as total_amount'),
            ])
            ->first();

        if ($idopprilStats && $idopprilStats->total_services > 0) {
            $insuranceStats->push([
                'insurance_name' => 'IDOPPRIL (Riesgo Laboral)',
                'total_services' => $idopprilStats->total_services,
                'total_amount' => '$' . number_format($idopprilStats->total_amount, 2),
            ]);
        }

        return $insuranceStats;
    }
}