<?php

namespace App\Actions;

use App\Models\Llamada;
use App\Models\AudioLlamada;
use Illuminate\Http\UploadedFile;
use App\Jobs\ProcessCallAIJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class UploadAudioAction
{
    public function execute($call_id, UploadedFile $file, array $metadata)
    {
        $llamada = Llamada::where('id', $call_id)->where('vendedor_id', Auth::id())->firstOrFail();

        // Validación de integridad: hash cruzado
        $serverHash = md5_file($file->path());
        $clientHash = $metadata['audio_hash'] ?? null;

        if ($clientHash && $serverHash !== $clientHash) {
            Log::warning("Hash mismatch en audio de llamada {$call_id}. Cliente: {$clientHash}, Servidor: {$serverHash}");
            throw new \Illuminate\Validation\ValidationException(
                validator: validator([], []),
                response: response()->json([
                    'success' => false,
                    'message' => 'El archivo recibido está corrupto o incompleto. El hash no coincide.',
                    'data' => [
                        'client_hash' => $clientHash,
                        'server_hash' => $serverHash,
                    ]
                ], 422)
            );
        }

        // Limpieza de audio previo si existe
        $existingAudio = AudioLlamada::where('llamada_id', $llamada->id)->first();
        if ($existingAudio && Storage::disk($existingAudio->storage_disk)->exists($existingAudio->storage_path)) {
            Storage::disk($existingAudio->storage_disk)->delete($existingAudio->storage_path);
        }

        $path = $file->store('audios', 'local');

        $audio = AudioLlamada::updateOrCreate(
            ['llamada_id' => $llamada->id],
            [
                'storage_disk' => 'local',
                'storage_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $metadata['mime_type'] ?? $file->getMimeType(),
                'file_size' => $file->getSize(),
                'file_hash' => $serverHash,
                'source_mode' => $metadata['source_mode'] ?? 'auto',
                'uploaded_at' => now()
            ]
        );

        $llamada->update(['estado_proceso' => 'audio_subido']);

        if ($llamada->dispositivo_id) {
            \App\Models\LogSincronizacion::create([
                'dispositivo_id' => $llamada->dispositivo_id,
                'llamada_id' => $llamada->id,
                'tipo_evento' => 'subida_audio',
                'payload_json' => [
                    'audio_id' => $audio->id,
                    'file_hash' => $serverHash,
                    'hash_validated' => $clientHash ? ($serverHash === $clientHash) : false,
                ],
                'resultado' => 'ok'
            ]);
        }

        ProcessCallAIJob::dispatch($llamada);

        return [
            'audio_id' => $audio->id,
            'estado_proceso' => $llamada->estado_proceso,
            'hash_validated' => $clientHash ? true : false,
        ];
    }
}

