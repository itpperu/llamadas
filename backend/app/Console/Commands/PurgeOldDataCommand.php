<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PurgeOldDataCommand extends Command
{
    protected $signature = 'system:purge 
                            {--days=90 : Días de antigüedad para purgar}
                            {--dry-run : Solo mostrar qué se eliminaría, sin ejecutar}
                            {--keep-audio : No eliminar archivos de audio}';

    protected $description = 'Purga datos antiguos según política de retención (llamadas, audios, logs, análisis)';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');
        $keepAudio = $this->option('keep-audio');
        $cutoff = Carbon::now()->subDays($days);

        $this->info("=== Política de Retención ===");
        $this->info("Fecha de corte: {$cutoff->format('Y-m-d H:i:s')} ({$days} días atrás)");
        
        if ($dryRun) {
            $this->warn("MODO DRY-RUN: No se eliminará nada.");
        }

        // 1. Obtener llamadas candidatas
        $llamadasIds = DB::table('llamadas')
            ->where('fecha_inicio', '<', $cutoff)
            ->pluck('id');

        $this->info("Llamadas candidatas a purga: {$llamadasIds->count()}");

        if ($llamadasIds->isEmpty()) {
            $this->info("No hay datos que purgar.");
            return 0;
        }

        // 2. Obtener audios asociados para eliminar archivos físicos
        $audiosCount = 0;
        if (!$keepAudio) {
            $audios = DB::table('audios_llamada')
                ->whereIn('llamada_id', $llamadasIds)
                ->get(['id', 'storage_disk', 'storage_path']);

            $audiosCount = $audios->count();
            $this->info("Archivos de audio a eliminar: {$audiosCount}");

            if (!$dryRun) {
                foreach ($audios as $audio) {
                    try {
                        if (Storage::disk($audio->storage_disk)->exists($audio->storage_path)) {
                            Storage::disk($audio->storage_disk)->delete($audio->storage_path);
                        }
                    } catch (\Exception $e) {
                        $this->error("Error eliminando audio {$audio->id}: {$e->getMessage()}");
                    }
                }
            }
        }

        // 3. Contar registros relacionados
        $analisCount = DB::table('analisis_llamada')->whereIn('llamada_id', $llamadasIds)->count();
        $logsCount = DB::table('logs_sincronizacion')->whereIn('llamada_id', $llamadasIds)->count();

        $this->info("Análisis a eliminar: {$analisCount}");
        $this->info("Logs de sincronización a eliminar: {$logsCount}");

        // 4. Purgar logs de sincronización huérfanos (no vinculados a llamada, pero antiguos)
        $logsOrphanCount = DB::table('logs_sincronizacion')
            ->where('created_at', '<', $cutoff)
            ->whereNull('llamada_id')
            ->count();
        $this->info("Logs huérfanos antiguos: {$logsOrphanCount}");

        if (!$dryRun) {
            // Orden: dependientes primero, luego llamadas
            DB::table('analisis_llamada')->whereIn('llamada_id', $llamadasIds)->delete();
            DB::table('audios_llamada')->whereIn('llamada_id', $llamadasIds)->delete();
            DB::table('logs_sincronizacion')->whereIn('llamada_id', $llamadasIds)->delete();
            DB::table('logs_sincronizacion')
                ->where('created_at', '<', $cutoff)
                ->whereNull('llamada_id')
                ->delete();
            DB::table('llamadas')->whereIn('id', $llamadasIds)->delete();

            // Limpiar clientes sin llamadas
            $orphanClients = DB::table('clientes')
                ->leftJoin('llamadas', 'clientes.id', '=', 'llamadas.cliente_id')
                ->whereNull('llamadas.id')
                ->pluck('clientes.id');
            
            if ($orphanClients->isNotEmpty()) {
                DB::table('clientes')->whereIn('id', $orphanClients)->delete();
                $this->info("Clientes huérfanos eliminados: {$orphanClients->count()}");
            }

            Log::info("Purga completada: {$llamadasIds->count()} llamadas, {$audiosCount} audios, {$analisCount} análisis, {$logsCount} logs eliminados. Corte: {$cutoff}");
        }

        $this->newLine();
        $this->info("=== Resumen ===");
        $this->table(
            ['Recurso', 'Cantidad', 'Estado'],
            [
                ['Llamadas', $llamadasIds->count(), $dryRun ? 'PENDIENTE' : 'ELIMINADO'],
                ['Audios (archivos)', $audiosCount, $keepAudio ? 'CONSERVADO' : ($dryRun ? 'PENDIENTE' : 'ELIMINADO')],
                ['Análisis IA', $analisCount, $dryRun ? 'PENDIENTE' : 'ELIMINADO'],
                ['Logs sincronización', $logsCount + $logsOrphanCount, $dryRun ? 'PENDIENTE' : 'ELIMINADO'],
            ]
        );

        return 0;
    }
}
