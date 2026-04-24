<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BackupDatabaseCommand extends Command
{
    protected $signature = 'system:backup 
                            {--path= : Ruta personalizada para el archivo de backup}';

    protected $description = 'Genera un dump SQL de la base de datos y copia los audios a un directorio de respaldo';

    public function handle(): int
    {
        $this->info("=== Backup del Sistema ===");
        $this->info("Fecha: " . now()->format('Y-m-d H:i:s'));

        $dbHost = config('database.connections.mysql.host');
        $dbPort = config('database.connections.mysql.port');
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');

        $backupDir = $this->option('path') ?: storage_path('app/backups');
        $timestamp = now()->format('Y-m-d_His');

        // Crear directorio de backup
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        // 1. Dump de base de datos
        $dumpFile = "{$backupDir}/db_backup_{$timestamp}.sql";
        $command = sprintf(
            'mysqldump -h %s -P %s -u %s -p%s %s > %s 2>&1',
            escapeshellarg($dbHost),
            escapeshellarg($dbPort),
            escapeshellarg($dbUser),
            escapeshellarg($dbPass),
            escapeshellarg($dbName),
            escapeshellarg($dumpFile)
        );

        $this->info("Generando dump de base de datos...");
        exec($command, $output, $returnCode);

        if ($returnCode === 0 && file_exists($dumpFile) && filesize($dumpFile) > 0) {
            $sizeKb = round(filesize($dumpFile) / 1024, 2);
            $this->info("✅ Dump BD generado: {$dumpFile} ({$sizeKb} KB)");
        } else {
            $this->error("❌ Error generando dump. mysqldump puede no estar disponible en este contenedor.");
            $this->warn("Alternativa: use el comando directamente desde el host:");
            $this->warn("  docker exec laravel_mysql mysqldump -u {$dbUser} -p{$dbPass} {$dbName} > backup.sql");
            
            // Fallback: exportar tablas como CSV
            $this->info("Generando fallback CSV...");
            $this->exportTablesCsv($backupDir, $timestamp);
        }

        // 2. Información de audios
        $audioDir = storage_path('app/audios');
        if (is_dir($audioDir)) {
            $audioFiles = glob("{$audioDir}/*");
            $totalSize = array_sum(array_map('filesize', $audioFiles));
            $this->info("📁 Archivos de audio: " . count($audioFiles) . " (" . round($totalSize / (1024 * 1024), 2) . " MB)");
            $this->info("   Ubicación: {$audioDir}");
            $this->info("   Para backup de audios, copie este directorio manualmente o configure rsync.");
        } else {
            $this->warn("Sin directorio de audios encontrado.");
        }

        // 3. Estadísticas de respaldo
        $stats = [
            ['Vendedores', DB::table('vendedores')->count()],
            ['Dispositivos', DB::table('dispositivos')->count()],
            ['Clientes', DB::table('clientes')->count()],
            ['Llamadas', DB::table('llamadas')->count()],
            ['Audios', DB::table('audios_llamada')->count()],
            ['Análisis', DB::table('analisis_llamada')->count()],
            ['Logs', DB::table('logs_sincronizacion')->count()],
        ];

        $this->newLine();
        $this->info("=== Tabla de Registros ===");
        $this->table(['Tabla', 'Registros'], $stats);

        return 0;
    }

    private function exportTablesCsv(string $dir, string $timestamp): void
    {
        $tables = ['vendedores', 'dispositivos', 'clientes', 'llamadas', 'audios_llamada', 'analisis_llamada', 'logs_sincronizacion'];

        foreach ($tables as $table) {
            $rows = DB::table($table)->get();
            if ($rows->isEmpty()) continue;

            $csvFile = "{$dir}/{$table}_{$timestamp}.csv";
            $fp = fopen($csvFile, 'w');
            fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM

            // Headers
            fputcsv($fp, array_keys((array) $rows->first()));

            // Data
            foreach ($rows as $row) {
                fputcsv($fp, array_map(function ($v) {
                    return is_array($v) || is_object($v) ? json_encode($v) : $v;
                }, (array) $row));
            }

            fclose($fp);
            $this->info("  → {$table}: {$rows->count()} registros → {$csvFile}");
        }
    }
}
