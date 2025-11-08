<?php

namespace App\Http\Controllers;

use App\Services\BackupService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BackupController extends Controller
{
    use ApiResponse;

    protected $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Crear un nuevo backup de la base de datos
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function create()
    {
        try {
            $result = $this->backupService->createBackup();
            
            Log::info('Backup creado exitosamente', [
                'filename' => $result['filename'],
                'size_mb' => $result['size_mb'],
            ]);

            return $this->successResponse($result, 201);

        } catch (\Exception $e) {
            Log::error('Error al crear backup', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse(
                'Error al crear el backup: ' . $e->getMessage(),
                'BACKUP_ERROR',
                500
            );
        }
    }

    /**
     * Listar todos los backups disponibles
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $backups = $this->backupService->listBackups();
            $stats = $this->backupService->getBackupStats();

            return $this->successResponse([
                'backups' => $backups,
                'stats' => $stats,
            ]);

        } catch (\Exception $e) {
            Log::error('Error al listar backups', [
                'error' => $e->getMessage(),
            ]);

            return $this->errorResponse(
                'Error al listar los backups: ' . $e->getMessage(),
                'BACKUP_LIST_ERROR',
                500
            );
        }
    }

    /**
     * Descargar un backup específico
     * 
     * @param string $filename
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\JsonResponse
     */
    public function download($filename)
    {
        try {
            // Validar nombre de archivo (seguridad)
            if (!preg_match('/^backup_[\w\-]+_\d{4}-\d{2}-\d{2}_\d{6}\.sql$/', $filename)) {
                return $this->errorResponse(
                    'Nombre de archivo inválido',
                    'INVALID_FILENAME',
                    400
                );
            }

            $filePath = $this->backupService->downloadBackup($filename);

            Log::info('Backup descargado', [
                'filename' => $filename,
            ]);

            return response()->download($filePath, $filename, [
                'Content-Type' => 'application/sql',
            ]);

        } catch (\Exception $e) {
            Log::error('Error al descargar backup', [
                'filename' => $filename,
                'error' => $e->getMessage(),
            ]);

            return $this->errorResponse(
                'Error al descargar el backup: ' . $e->getMessage(),
                'BACKUP_DOWNLOAD_ERROR',
                404
            );
        }
    }

    /**
     * Eliminar un backup específico
     * 
     * @param string $filename
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($filename)
    {
        try {
            // Validar nombre de archivo (seguridad)
            if (!preg_match('/^backup_[\w\-]+_\d{4}-\d{2}-\d{2}_\d{6}\.sql$/', $filename)) {
                return $this->errorResponse(
                    'Nombre de archivo inválido',
                    'INVALID_FILENAME',
                    400
                );
            }

            $this->backupService->deleteBackup($filename);

            Log::info('Backup eliminado', [
                'filename' => $filename,
            ]);

            return $this->successResponse([
                'message' => 'Backup eliminado exitosamente',
                'filename' => $filename,
            ]);

        } catch (\Exception $e) {
            Log::error('Error al eliminar backup', [
                'filename' => $filename,
                'error' => $e->getMessage(),
            ]);

            return $this->errorResponse(
                'Error al eliminar el backup: ' . $e->getMessage(),
                'BACKUP_DELETE_ERROR',
                404
            );
        }
    }

    /**
     * Limpiar backups antiguos
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clean(Request $request)
    {
        try {
            $keep = $request->get('keep', 10);

            // Validar que sea un número positivo
            if (!is_numeric($keep) || $keep < 1) {
                return $this->errorResponse(
                    'El parámetro "keep" debe ser un número positivo',
                    'INVALID_PARAMETER',
                    400
                );
            }

            $result = $this->backupService->cleanOldBackups((int) $keep);

            Log::info('Backups antiguos limpiados', [
                'deleted_count' => $result['deleted_count'],
                'remaining' => $result['remaining'],
            ]);

            return $this->successResponse($result);

        } catch (\Exception $e) {
            Log::error('Error al limpiar backups antiguos', [
                'error' => $e->getMessage(),
            ]);

            return $this->errorResponse(
                'Error al limpiar backups antiguos: ' . $e->getMessage(),
                'BACKUP_CLEAN_ERROR',
                500
            );
        }
    }

    /**
     * Obtener estadísticas de backups
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats()
    {
        try {
            $stats = $this->backupService->getBackupStats();
            return $this->successResponse($stats);

        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas de backups', [
                'error' => $e->getMessage(),
            ]);

            return $this->errorResponse(
                'Error al obtener estadísticas: ' . $e->getMessage(),
                'BACKUP_STATS_ERROR',
                500
            );
        }
    }
}