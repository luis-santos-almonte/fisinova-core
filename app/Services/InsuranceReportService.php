<?php

namespace App\Services;

use App\Models\Authorization;
use App\Models\Insurance;
use Illuminate\Support\Facades\DB;
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
        $reportData = $this->getReportData($filters);

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
        $isIdoppril = isset($filters['is_idoppril']) && $filters['is_idoppril'];

        // Si es IDOPPRIL, buscar por payment_type
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
            $insurance = Insurance::findOrFail($filters['insurance_id']);
            $authorizations = $this->getInsuranceAuthorizations($filters);
        }

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
    }

    /**
     * Obtener autorizaciones de IDOPPRIL (riesgo laboral)
     */
    protected function getIdopprilAuthorizations(array $filters)
    {
        $query = DB::table('authorizations as auth')
            ->join('patients as p', 'auth.patient_id', '=', 'p.id')
            ->leftJoin('appointments as app', 'auth.appointment_id', '=', 'app.id')
            ->whereBetween('auth.authorization_date', [$filters['start_date'], $filters['end_date']])
            ->where('auth.active', true);

        // IDOPPRIL: buscar por case_number O por payment_type
        $query->where(function ($q) {
            $q->whereNotNull('auth.case_number')
                ->orWhere('auth.payment_type', 'workplace_risk');
        });

        return $query->select([
            'auth.id',
            'auth.authorization_date',
            'auth.authorization_number',
            'auth.insurance_amount',
            'auth.patient_amount',
            'auth.total_amount',
            'auth.patient_name',
            'auth.patient_last_name',
            'auth.case_number',
            'auth.patient_id',
            DB::raw("COALESCE(app.type, 'consultation') as service_type"),
            DB::raw("CASE 
            WHEN app.type = 'consultation' THEN 'CONSULTA'
            WHEN app.type = 'therapy' THEN 'TERAPIA'
            WHEN app.type = 'admission' THEN 'INTERNAMIENTO'
            ELSE 'CONSULTA'
        END as procedure_description")
        ])
            ->orderBy('auth.authorization_date', 'asc')
            ->get();
    }
    /**
     * Obtener autorizaciones de un seguro normal
     */
    protected function getInsuranceAuthorizations(array $filters)
    {
        return DB::table('authorizations as auth')
            ->join('patients as p', 'auth.patient_id', '=', 'p.id')
            ->leftJoin('appointments as app', 'auth.appointment_id', '=', 'app.id')
            ->where('auth.insurance_id', $filters['insurance_id'])
            ->whereBetween('auth.authorization_date', [$filters['start_date'], $filters['end_date']])
            ->where('auth.active', true)
            ->select([
                'auth.id',
                'auth.authorization_date',
                'auth.authorization_number',
                'auth.insurance_amount',
                'auth.patient_amount',
                'auth.total_amount',
                'auth.patient_name',
                'auth.patient_last_name',
                'auth.patient_insurance_code',
                'auth.patient_id',
                DB::raw("COALESCE(app.type, 'consultation') as service_type"),
                DB::raw("CASE 
                    WHEN app.type = 'consultation' THEN 'CONSULTA'
                    WHEN app.type = 'therapy' THEN 'TERAPIA'
                    WHEN app.type = 'admission' THEN 'INTERNAMIENTO'
                    ELSE 'CONSULTA'
                END as procedure_description")
            ])
            ->orderBy('auth.authorization_date', 'asc')
            ->get();
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
        $pdf = Pdf::loadView('reports.insurance-report', $reportData);
        $pdf->setPaper('letter', 'portrait');

        return $pdf->download($this->generateFilename($reportData, 'pdf'));
    }

    /**
     * Generar Excel
     */
    protected function generateExcel($reportData)
    {
        return Excel::download(
            new InsuranceReportExport($reportData),
            $this->generateFilename($reportData, 'xlsx')
        );
    }

    /**
     * Generar nombre de archivo
     */
    protected function generateFilename($reportData, $extension)
    {
        $insurance = $reportData['insurance'];
        $insuranceName = str_replace(' ', '_', $insurance->name);
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
                DB::raw('SUM(insurance_amount) as total_insurance_amount'),
                DB::raw('SUM(patient_amount) as total_patient_amount'),
                DB::raw('SUM(total_amount) as total_amount'),
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
                DB::raw('SUM(total_amount) as total_amount'),
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
                DB::raw('SUM(total_amount) as total_amount'),
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
