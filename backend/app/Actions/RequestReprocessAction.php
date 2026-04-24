<?php

namespace App\Actions;

use App\Models\Llamada;
use App\Jobs\ProcessCallAIJob;

class RequestReprocessAction
{
    public function execute($call_id)
    {
        $llamada = Llamada::findOrFail($call_id);
        
        $llamada->update(['estado_proceso' => 'transcripcion_pendiente']);

        if ($llamada->dispositivo_id) {
            \App\Models\LogSincronizacion::create([
                'dispositivo_id' => $llamada->dispositivo_id,
                'llamada_id' => $llamada->id,
                'tipo_evento' => 'solicitud_reproceso',
                'payload_json' => [],
                'resultado' => 'ok'
            ]);
        }
        
        ProcessCallAIJob::dispatch($llamada);
        
        return true;
    }
}
