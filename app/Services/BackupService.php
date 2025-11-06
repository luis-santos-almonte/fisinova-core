<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Exception;

class BackupService
{
    /**
     * Directorio donde se almacenarán los backups
     */
    const BACKUP_DIR = 'backups';

    /**
     * Crear un backup de la base de datos PostgreSQL
     * 
     * @return array
     * @throws Exception
     */
    public function createBackup(): array
    {
        try {
            // Obtener configuración de la base de datos
            $database = Config::get('database.connections.pgsql.database');
            $username = Config::get('database.connections.pgsql.username');
            $password = Config::get('database.connections.pgsql.password');
            $host = Config::get('database.connections.pgsql.host');
            $port = Config::get('database.connections.pgsql.port');

            // Generar nombre del archivo
            $timestamp = Carbon::now('America/Santo_Domingo')->format('Y-m-d_His');
            $filename = "backup_{$database}_{$timestamp}.sql";
            
            // Ruta completa del archivo
            $backupPath = storage_path('app/' . self::BACKUP_DIR);
            
            // Crear directorio si no existe
            if (!file_exists($backupPath)) {
                mkdir($backupPath, 0755, true);
            }

            $fullPath = $backupPath . '/' . $filename;

            // Establecer variable de entorno para la contraseña
            putenv("PGPASSWORD={$password}");

            // Comando pg_dump para PostgreSQL
            $command = sprintf(
                'pg_dump -h %s -p %s -U %s -F p -b -v -f %s %s 2>&1',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($fullPath),
                escapeshellarg($database)
            );

            // Ejecutar el comando
            exec($command, $output, $returnCode);

            // Limpiar variable de entorno
            putenv("PGPASSWORD");

            // Verificar si el comando fue exitoso
            if ($returnCode !== 0) {
                throw new Exception('Error al ejecutar pg_dump: ' . implode("\n", $output));
            }

            // Verificar que el archivo se haya creado
            if (!file_exists($fullPath)) {
                throw new Exception('El archivo de backup no se creó correctamente');
            }

            // Obtener información del archivo
            $fileSize = filesize($fullPath);
            $fileSizeMB = round($fileSize / 1024 / 1024, 2);

            return [
                'success' => true,
                'message' => 'Backup creado exitosamente',
                'filename' => $filename,
                'path' => $fullPath,
                'size' => $fileSize,
                'size_mb' => $fileSizeMB,
                'database' => $database,
                'created_at' => Carbon::now('America/Santo_Domingo')->toDateTimeString(),
            ];

        } catch (Exception $e) {
            throw new Exception('Error al crear el backup: ' . $e->getMessage());
        }
    }

    /**
     * Listar todos los backups disponibles
     * 
     * @return array
     */
    public function listBackups(): array
    {
        $backupPath = storage_path('app/' . self::BACKUP_DIR);
        
        if (!file_exists($backupPath)) {
            return [];
        }

        $files = glob($backupPath . '/*.sql');
        $backups = [];

        foreach ($files as $file) {
            $filename = basename($file);
            $fileSize = filesize($file);
            $fileSizeMB = round($fileSize / 1024 / 1024, 2);
            $createdAt = Carbon::createFromTimestampUTC(filemtime($file))
    ->setTimezone('America/Santo_Domingo');

            $backups[] = [
                'filename' => $filename,
                'path' => $file,
                'size' => $fileSize,
                'size_mb' => $fileSizeMB,
                'created_at' => $createdAt->toDateTimeString(),
                'created_at_human' => $createdAt->diffForHumans(),
            ];
        }

        // Ordenar por fecha de creación (más reciente primero)
        usort($backups, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return $backups;
    }

    /**
     * Eliminar un backup específico
     * 
     * @param string $filename
     * @return bool
     * @throws Exception
     */
    public function deleteBackup(string $filename): bool
    {
        $backupPath = storage_path('app/' . self::BACKUP_DIR . '/' . $filename);

        if (!file_exists($backupPath)) {
            throw new Exception('El archivo de backup no existe');
        }

        if (!unlink($backupPath)) {
            throw new Exception('No se pudo eliminar el archivo de backup');
        }

        return true;
    }

    /**
     * Descargar un backup específico
     * 
     * @param string $filename
     * @return string Path del archivo
     * @throws Exception
     */
    public function downloadBackup(string $filename): string
    {
        $backupPath = storage_path('app/' . self::BACKUP_DIR . '/' . $filename);

        if (!file_exists($backupPath)) {
            throw new Exception('El archivo de backup no existe');
        }

        return $backupPath;
    }

    /**
     * Limpiar backups antiguos (mantener solo los últimos N)
     * 
     * @param int $keep Número de backups a mantener
     * @return array
     */
    public function cleanOldBackups(int $keep = 10): array
    {
        $backups = $this->listBackups();
        $deleted = [];

        if (count($backups) > $keep) {
            $toDelete = array_slice($backups, $keep);

            foreach ($toDelete as $backup) {
                try {
                    $this->deleteBackup($backup['filename']);
                    $deleted[] = $backup['filename'];
                } catch (Exception $e) {
                    // Log error pero continuar
                    \Log::error('Error eliminando backup antiguo: ' . $e->getMessage());
                }
            }
        }

        return [
            'total_backups' => count($backups),
            'deleted_count' => count($deleted),
            'deleted_files' => $deleted,
            'remaining' => count($backups) - count($deleted),
        ];
    }

    /**
     * Obtener estadísticas de backups
     * 
     * @return array
     */
    public function getBackupStats(): array
    {
        $backups = $this->listBackups();
        $totalSize = array_sum(array_column($backups, 'size'));
        $totalSizeMB = round($totalSize / 1024 / 1024, 2);

        return [
            'total_backups' => count($backups),
            'total_size' => $totalSize,
            'total_size_mb' => $totalSizeMB,
            'oldest_backup' => !empty($backups) ? end($backups)['created_at'] : null,
            'newest_backup' => !empty($backups) ? $backups[0]['created_at'] : null,
        ];
    }
}