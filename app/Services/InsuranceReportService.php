<?php

namespace App\Services;

use App\Models\Authorization;
use App\Models\Insurance;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InsuranceReportExport;

class InsuranceReportService
{
    // Datos de la empresa
    const COMPANY_NAME = 'Centro de Rehabilitación Física Fisinova';
    const COMPANY_RNC = '131-66268-4';
    const COMPANY_PHONE = '809-123-4567';
    const COMPANY_CITY = 'La Vega';
    
    /**
     * Generar reporte en el formato solicitado (PDF o Excel)
     * NO SE GUARDA - se genera en tiempo real
     */
    public function generateReport(array $filters, string $format = 'pdf')
    {
        // Obtener datos del reporte
        $reportData = $this->getReportData($filters);
        
        // Generar según formato
        if ($format === 'pdf') {
            return $this->generatePDF($reportData);
        }
        
        return $this->generateExcel($reportData);
    }

    /**
     * Obtener vista previa de datos
     */
    public function getPreviewData(array $filters)
    {
        return $this->getReportData($filters);
    }

    /**
     * Obtener datos del reporte ORGANIZADOS POR PACIENTE
     */
    protected function getReportData(array $filters)
    {
        $insurance = Insurance::findOrFail($filters['insurance_id']);
        $isWorkplaceRisk = in_array($insurance->code, ['ARL', 'IDOPPRIL']);
        
        // Obtener autorizaciones del período
        $authorizations = Authorization::forReport(
            $filters['insurance_id'],
            $filters['start_date'],
            $filters['end_date']
        )->get();

        // Agrupar por paciente y ordenar servicios
        $groupedByPatient = $this->groupAndSortByPatient($authorizations);
        
        // Calcular totales
        $summary = [
            'total_services' => $authorizations->count(),
            'total_insurance_amount' => $authorizations->sum('insurance_amount'),
            'total_patient_amount' => $authorizations->sum('patient_amount'),
            'total_amount' => $authorizations->sum('total_amount'),
            'consultations_count' => $authorizations->where('service_type', 'consultation')->count(),
            'therapies_count' => $authorizations->where('service_type', 'therapy')->count(),
            'admissions_count' => $authorizations->where('service_type', 'admission')->count(),
        ];
        
        return [
            'services' => $groupedByPatient,
            'summary' => $summary,
            'insurance' => $insurance,
            'is_workplace_risk' => $isWorkplaceRisk,
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
    }

    /**
     * Agrupar por paciente y ordenar:
     * Prioridad: Consulta -> Internamiento -> Terapia
     */
    protected function groupAndSortByPatient($authorizations)
    {
        $grouped = $authorizations->groupBy('patient_id')->map(function ($patientAuths) {
            // Ordenar servicios por prioridad
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
        $pdf = Pdf::loadView('reports.insurance-report', $reportData);
        $pdf->setPaper('letter', 'portrait');
        
        return $pdf->download($this->generateFilename($reportData['insurance'], 'pdf'));
    }

    /**
     * Generar Excel
     */
    protected function generateExcel($reportData)
    {
        return Excel::download(
            new InsuranceReportExport($reportData),
            $this->generateFilename($reportData['insurance'], 'xlsx')
        );
    }

    /**
     * Generar nombre de archivo
     */
    protected function generateFilename($insurance, $extension)
    {
        $insuranceName = str_replace(' ', '_', $insurance->name);
        $date = now()->format('Ymd_His');
        
        return "Reporte_{$insuranceName}_{$date}.{$extension}";
    }

    /**
     * Obtener estadísticas para el dashboard
     */
    public function getStats($startDate, $endDate)
    {
        $stats = Authorization::whereBetween('authorization_date', [$startDate, $endDate])
            ->where('active', true)
            ->select([
                DB::raw('COUNT(*) as total_services'),
                DB::raw('COUNT(DISTINCT patient_id) as total_patients'),
                DB::raw('SUM(insurance_amount) as total_insurance_amount'),
                DB::raw('SUM(patient_amount) as total_patient_amount'),
                DB::raw('SUM(total_amount) as total_amount'),
            ])
            ->first();
        
        return [
            'current_month_amount' => '$' . number_format($stats->total_amount ?? 0, 2),
            'services_performed' => $stats->total_services ?? 0,
            'patients_attended' => $stats->total_patients ?? 0,
            'insurance_amount' => '$' . number_format($stats->total_insurance_amount ?? 0, 2),
            'patient_amount' => '$' . number_format($stats->total_patient_amount ?? 0, 2),
        ];
    }

    /**
     * Obtener estadísticas por seguro
     */
    public function getStatsByInsurance($startDate, $endDate)
    {
        return Authorization::with('insurance')
            ->whereBetween('authorization_date', [$startDate, $endDate])
            ->where('active', true)
            ->select([
                'insurance_id',
                DB::raw('COUNT(*) as total_services'),
                DB::raw('SUM(insurance_amount) as total_amount'),
            ])
            ->groupBy('insurance_id')
            ->get()
            ->map(function ($stat) {
                return [
                    'insurance_name' => $stat->insurance->name,
                    'total_services' => $stat->total_services,
                    'total_amount' => '$' . number_format($stat->total_amount, 2),
                ];
            });
    }
}