<?php

namespace App\Http\Controllers;

use App\Http\Requests\Insurance\InsuranceReportRequest;
use App\Services\InsuranceReportService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class InsuranceReportController extends Controller
{
    use ApiResponse;

    protected $reportService;

    public function __construct(InsuranceReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Vista previa de datos sin generar archivo
     */
    public function preview(InsuranceReportRequest $request)
    {
        try {
            $data = $request->validated();
            
            Log::info('Preview request received', [
                'data' => $data,
                'user' => $request->user()->id
            ]);
            
            $previewData = $this->reportService->getPreviewData($data);
            
            Log::info('Preview data generated', [
                'services_count' => count($previewData['services']),
                'total_amount' => $previewData['summary']['total_amount']
            ]);
            
            // Retornar con formato de ApiResponse usando el trait
            return $this->successResponse($previewData);
            
        } catch (\Exception $e) {
            Log::error('Error en vista previa de reporte', [
                'error' => $e->getMessage(),
                'filters' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->errorResponse(
                'Error al generar vista previa: ' . $e->getMessage(),
                'PREVIEW_ERROR',
                500
            );
        }
    }

    /**
     * Generar y descargar reporte inmediatamente
     */
    public function download(InsuranceReportRequest $request)
    {
        try {
            $data = $request->validated();
            $format = $data['format'] ?? 'pdf';
            
            Log::info('Download request received', [
                'format' => $format,
                'is_idoppril' => $data['is_idoppril'] ?? false,
                'insurance_id' => $data['insurance_id'] ?? null
            ]);
            
            // Este método retorna directamente la descarga
            return $this->reportService->generateReport($data, $format);
            
        } catch (\Exception $e) {
            Log::error('Error generando reporte', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'filters' => $request->all()
            ]);
            
            return $this->errorResponse(
                'Error al generar el reporte: ' . $e->getMessage(),
                'DOWNLOAD_ERROR',
                500
            );
        }
    }

    /**
     * Estadísticas para reportería (solo para el módulo de reportes)
     */
    public function reportStats(Request $request)
    {
        try {
            // Por defecto: mes actual
            $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
            $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());
            
            Log::info('Stats request', [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            
            $stats = $this->reportService->getReportStats($startDate, $endDate);
            
            return $this->successResponse($stats);
            
        } catch (\Exception $e) {
            Log::error('Error obteniendo estadísticas de reportería', [
                'error' => $e->getMessage()
            ]);
            
            return $this->errorResponse(
                'Error al obtener estadísticas',
                'STATS_ERROR',
                500
            );
        }
    }

    /**
     * Estadísticas agrupadas por seguro + IDOPPRIL
     */
    public function statsByInsurance(Request $request)
    {
        try {
            // Por defecto: mes actual
            $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
            $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());
            
            $stats = $this->reportService->getStatsByInsurance($startDate, $endDate);
            
            return $this->successResponse($stats);
            
        } catch (\Exception $e) {
            Log::error('Error obteniendo estadísticas por seguro', [
                'error' => $e->getMessage()
            ]);
            
            return $this->errorResponse(
                'Error al obtener estadísticas por seguro',
                'STATS_BY_INSURANCE_ERROR',
                500
            );
        }
    }
}