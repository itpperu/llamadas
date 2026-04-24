<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Llamada;
use App\Models\AnalisisLlamada;
use App\Services\AIWorkerService;
use Illuminate\Support\Facades\Log;

class ProcessCallAIJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [30, 60, 120]; // Backoff progresivo: 30s, 1min, 2min
    public $timeout = 120; // Timeout más generoso para Whisper

    protected $llamada;

    public function __construct(Llamada $llamada)
    {
        $this->llamada = $llamada;
    }

    public function handle(AIWorkerService $aiService): void
    {
        $startTime = microtime(true);
        $callId = $this->llamada->id;

        Log::channel('stack')->info("[AI-JOB] Iniciando procesamiento", [
            'call_id' => $callId,
            'attempt' => $this->attempts(),
            'vendedor_id' => $this->llamada->vendedor_id,
            'estado_previo' => $this->llamada->estado_proceso,
        ]);

        try {
            $this->llamada->update(['estado_proceso' => 'analisis_pendiente']);

            $resultado = $aiService->analyzeCall($this->llamada);

            $this->llamada->update([
                'transcript_text' => $resultado['transcript_text'] ?? null,
                'summary_text' => $resultado['summary_text'] ?? null,
                'estado_proceso' => 'analizada'
            ]);

            if (isset($resultado['analisis'])) {
                $analisisData = $resultado['analisis'];
                AnalisisLlamada::updateOrCreate(
                    ['llamada_id' => $this->llamada->id],
                    [
                        'modelo_version' => $analisisData['modelo_version'] ?? 'unknown',
                        'sentimiento_cliente' => $analisisData['sentimiento_cliente'] ?? null,
                        'tono_general' => $analisisData['tono_general'] ?? null,
                        'intencion_comercial' => $analisisData['intencion_comercial'] ?? null,
                        'score_venta' => $analisisData['score_venta'] ?? null,
                        'objeciones_json' => $analisisData['objeciones_json'] ?? null,
                        'siguiente_accion' => $analisisData['siguiente_accion'] ?? null,
                        'analisis_json' => $resultado,
                        'analizado_at' => now()
                    ]
                );
            }

            $elapsed = round(microtime(true) - $startTime, 2);
            Log::channel('stack')->info("[AI-JOB] ✅ Procesamiento exitoso", [
                'call_id' => $callId,
                'tiempo_segundos' => $elapsed,
                'score_venta' => $resultado['analisis']['score_venta'] ?? null,
                'sentimiento' => $resultado['analisis']['sentimiento_cliente'] ?? null,
                'transcript_length' => strlen($resultado['transcript_text'] ?? ''),
            ]);

        } catch (\Exception $e) {
            $elapsed = round(microtime(true) - $startTime, 2);
            $this->llamada->update(['estado_proceso' => 'error']);

            Log::channel('stack')->error("[AI-JOB] ❌ Error en procesamiento", [
                'call_id' => $callId,
                'attempt' => $this->attempts(),
                'max_tries' => $this->tries,
                'tiempo_segundos' => $elapsed,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
            ]);

            // Registrar en log de sincronización para trazabilidad web
            if ($this->llamada->dispositivo_id) {
                \App\Models\LogSincronizacion::create([
                    'dispositivo_id' => $this->llamada->dispositivo_id,
                    'llamada_id' => $this->llamada->id,
                    'tipo_evento' => 'error_ia',
                    'payload_json' => [
                        'error' => substr($e->getMessage(), 0, 500),
                        'attempt' => $this->attempts(),
                    ],
                    'resultado' => 'error'
                ]);
            }

            throw $e;
        }
    }

    /**
     * Determine the amount of time the job should be delayed when an exception is thrown.
     */
    public function failed(\Throwable $exception): void
    {
        Log::channel('stack')->critical("[AI-JOB] 💀 Job definitivamente fallido (todos los reintentos agotados)", [
            'call_id' => $this->llamada->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
