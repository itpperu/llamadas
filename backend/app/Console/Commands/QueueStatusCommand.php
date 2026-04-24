<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class QueueStatusCommand extends Command
{
    protected $signature = 'system:queue-status';

    protected $description = 'Muestra el estado actual de la cola de trabajos';

    public function handle(): int
    {
        $this->info("=== Estado de Cola de Trabajos ===");
        $this->info("Fecha: " . now()->format('Y-m-d H:i:s'));
        $this->newLine();

        // Jobs pendientes
        $pending = DB::table('jobs')->count();
        $pendingOldest = DB::table('jobs')->min('created_at');

        // Jobs fallidos
        $failed = DB::table('failed_jobs')->count();
        $failedLatest = DB::table('failed_jobs')->max('failed_at');

        // Jobs por tipo (si hay pendientes)
        $jobsByType = DB::table('jobs')
            ->selectRaw("payload, COUNT(*) as total")
            ->groupBy('payload')
            ->get()
            ->map(function ($row) {
                $payload = json_decode($row->payload, true);
                $class = $payload['displayName'] ?? 'Desconocido';
                return ['type' => class_basename($class), 'total' => $row->total];
            });

        // Jobs fallidos recientes
        $recentFailed = DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->limit(5)
            ->get(['id', 'payload', 'exception', 'failed_at']);

        // Estado del sistema
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Driver de cola', config('queue.default')],
                ['Jobs pendientes', $pending],
                ['Jobs fallidos (total)', $failed],
                ['Pendiente más antiguo', $pendingOldest ? Carbon::parse($pendingOldest)->diffForHumans() : 'N/A'],
                ['Último fallo', $failedLatest ? Carbon::parse($failedLatest)->diffForHumans() : 'N/A'],
            ]
        );

        // Desglose por tipo
        if ($jobsByType->isNotEmpty()) {
            $this->newLine();
            $this->info("📋 Jobs pendientes por tipo:");
            $this->table(
                ['Tipo', 'Cantidad'],
                $jobsByType->map(fn($j) => [$j['type'], $j['total']])->toArray()
            );
        }

        // Últimos fallos
        if ($recentFailed->isNotEmpty()) {
            $this->newLine();
            $this->warn("⚠️  Últimos 5 jobs fallidos:");
            foreach ($recentFailed as $f) {
                $payload = json_decode($f->payload, true);
                $class = class_basename($payload['displayName'] ?? 'Desconocido');
                $exceptionLines = explode("\n", $f->exception);
                $errorMsg = substr($exceptionLines[0] ?? '', 0, 120);
                
                $this->line("  [{$f->id}] {$class} — Falló {$f->failed_at}");
                $this->line("       Error: {$errorMsg}");
            }
        }

        // Alertas
        $this->newLine();
        if ($pending > 50) {
            $this->error("🔴 ALERTA: Hay {$pending} jobs acumulados. Verifique que el worker está activo.");
        } elseif ($pending > 10) {
            $this->warn("🟡 AVISO: Hay {$pending} jobs en cola. Puede haber procesamiento lento.");
        } else {
            $this->info("🟢 Cola saludable.");
        }

        if ($failed > 0) {
            $this->warn("⚠️  Hay {$failed} jobs fallidos. Ejecute: php artisan queue:retry all");
        }

        return 0;
    }
}
