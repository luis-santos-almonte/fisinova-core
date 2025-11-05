<?php

namespace App\Http\Controllers;

use App\Http\Requests\Insurance\InsuranceReportRequest;
use App\Services\InsuranceReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class InsuranceReportController extends Controller
{
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
            $previewData = $this->reportService->getPreviewData($data);
            
            return response()->json([
                'success' => true,
                'data' => $previewData
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en vista previa de reporte', [
                'error' => $e->getMessage(),
                'filters' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al generar vista previa: ' . $e->getMessage()
            ], 500);
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
            
            return $this->reportService->generateReport($data, $format);
            
        } catch (\Exception $e) {
            Log::error('Error generando reporte', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'filters' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el reporte: ' . $e->getMessage()
            ], 500);
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
            
            $stats = $this->reportService->getReportStats($startDate, $endDate);
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error obteniendo estadísticas de reportería', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas'
            ], 500);
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
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error obteniendo estadísticas por seguro', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas por seguro'
            ], 500);
        }
    }
}