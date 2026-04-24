<?php

namespace App\Services;

use App\Models\Llamada;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIWorkerService
{
    public function analyzeCall(Llamada $llamada): array
    {
        $audioPath = null;
        if ($llamada->audio) {
            // Mandamos la ruta absoluta del servidor (Storage::path)
            $audioPath = \Illuminate\Support\Facades\Storage::path($llamada->audio->storage_path);
        }

        // El endpoint ahora es /process-call según la nueva estructura Python-AI
        $url = env('AI_WORKER_URL', 'http://host.docker.internal:8001/process-call');
        
        Log::info("Enviando llamada {$llamada->id} a servicio AI: {$url}");

        try {
            Log::info("Payload enviado a Python-AI:", [
                'call_id' => $llamada->id,
                'audio_path' => $audioPath,
                'metadata' => [
                    'duracion' => $llamada->duracion_segundos,
                    'tipo' => $llamada->tipo_llamada,
                    'telefono_cliente' => $llamada->telefono_cliente_normalizado
                ]
            ]);

            $response = Http::timeout(60)->post($url, [
                'call_id' => $llamada->id,
                'audio_path' => $audioPath,
                'metadata' => [
                    'duracion' => $llamada->duracion_segundos,
                    'tipo' => $llamada->tipo_llamada,
                    'telefono_cliente' => $llamada->telefono_cliente_normalizado
                ]
            ]);

            if (!$response->successful()) {
                Log::error("Error en Microservicio AI. Status: {$response->status()}. Respuesta: {$response->body()}");
                throw new \Exception("El microservicio AI respondió con error (HTTP {$response->status()})");
            }

            $data = $response->json();

            // Mapeamos el contrato del Microservicio al formato que espera nuestro ProcessCallAIJob
            return [
                'transcript_text' => $data['transcript'] ?? null,
                'summary_text' => $data['resumen'] ?? null,
                'analisis' => [
                    'modelo_version' => $data['modelo_version'] ?? 'whisper-v1',
                    'sentimiento_cliente' => $data['sentimiento'] ?? 'neutral',
                    'tono_general' => $data['sentimiento'] === 'positivo' ? 'profesional' : 'tenso',
                    'intencion_comercial' => ($data['probabilidad_venta'] > 50) ? 'alta' : 'baja',
                    'score_venta' => $data['probabilidad_venta'] ?? 0,
                    'objeciones_json' => $data['objeciones'] ?? [],
                    'siguiente_accion' => $data['siguiente_accion'] ?? 'Sin seguimiento definido'
                ]
            ];

        } catch (\Exception $e) {
            Log::error("Fallo comunicación con microservicio AI: " . $e->getMessage());
            throw $e;
        }
    }
}
