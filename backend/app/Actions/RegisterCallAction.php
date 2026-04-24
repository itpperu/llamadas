<?php

namespace App\Actions;

use App\Models\Llamada;
use App\Models\Cliente;
use App\Models\Dispositivo;

class RegisterCallAction
{
    public function execute(array $data)
    {
        $telefonoNormalizado = preg_replace('/[^0-9+]/', '', $data['telefono_cliente']);

        $cliente = Cliente::firstOrCreate(
            ['telefono_normalizado' => $telefonoNormalizado],
            ['nombre_referencial' => 'Cliente ' . $telefonoNormalizado]
        );

        $dispositivo = Dispositivo::where('device_uuid', $data['device_uuid'])
            ->where('vendedor_id', \Illuminate\Support\Facades\Auth::id())
            ->firstOrFail();

        $llamada = Llamada::firstOrCreate(
            [
                'dispositivo_id' => $dispositivo->id,
                'vendedor_id' => $data['vendedor_id'],
                'telefono_cliente_normalizado' => $telefonoNormalizado,
                'fecha_inicio' => $data['fecha_inicio']
            ],
            [
                'cliente_id' => $cliente->id,
                'telefono_origen' => 'desconocido', 
                'telefono_destino' => $data['telefono_cliente'],
                'tipo_llamada' => $data['tipo'],
                'fecha_fin' => $data['fecha_fin'],
                'duracion_segundos' => $data['duracion_segundos'],
                'estado_proceso' => (isset($data['estado_audio']) && $data['estado_audio'] === 'pendiente') ? 'audio_pendiente' : 'registrada'
            ]
        );

        \App\Models\LogSincronizacion::create([
            'dispositivo_id' => $dispositivo->id,
            'llamada_id' => $llamada->id,
            'tipo_evento' => 'registro_llamada',
            'payload_json' => $data,
            'resultado' => 'ok'
        ]);

        return [
            'call_id' => $llamada->id,
            'estado_proceso' => $llamada->estado_proceso
        ];
    }
}
