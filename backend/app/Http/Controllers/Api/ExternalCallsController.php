<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Llamada;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ExternalCallsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'phone'       => ['nullable', 'string'],
            'cliente_id'  => ['nullable', 'integer'],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
        ]);

        if (!$request->filled('phone') && !$request->filled('cliente_id')) {
            return response()->json([
                'error' => 'Debe proporcionar phone o cliente_id.',
            ], 422);
        }

        // Resolver cliente por teléfono normalizado o por ID
        $cliente = null;
        if ($request->filled('phone')) {
            $normalizado = $this->normalizePhone($request->phone);
            $cliente = Cliente::where('telefono_normalizado', $normalizado)->first();
            if (!$cliente) {
                return response()->json([
                    'error'   => 'No se encontró ningún cliente con ese número.',
                    'phone'   => $normalizado,
                ], 404);
            }
        } else {
            $cliente = Cliente::find($request->cliente_id);
            if (!$cliente) {
                return response()->json(['error' => 'Cliente no encontrado.'], 404);
            }
        }

        $query = Llamada::with(['vendedor:id,nombre', 'analisis'])
            ->where('cliente_id', $cliente->id)
            ->orderBy('fecha_inicio', 'desc');

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_inicio', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_inicio', '<=', $request->fecha_hasta);
        }

        $llamadas = $query->get();

        return response()->json([
            'success' => true,
            'cliente' => [
                'id'      => $cliente->id,
                'telefono' => $cliente->telefono_normalizado,
                'nombre'  => $cliente->nombre_referencial,
            ],
            'periodo' => [
                'fecha_desde' => $request->fecha_desde,
                'fecha_hasta' => $request->fecha_hasta,
            ],
            'total'    => $llamadas->count(),
            'llamadas' => $llamadas->map(fn($call) => $this->formatCall($call)),
        ]);
    }

    private function formatCall(Llamada $call): array
    {
        return [
            'id'                => $call->id,
            'fecha_inicio'      => $call->fecha_inicio?->toIso8601String(),
            'fecha_fin'         => $call->fecha_fin?->toIso8601String(),
            'tipo'              => $call->tipo_llamada,
            'duracion_segundos' => $call->duracion_segundos,
            'estado'            => $call->estado_proceso,
            'vendedor'          => $call->vendedor?->nombre,
            'transcript'        => $call->transcript_text,
            'resumen'           => $call->summary_text,
            'analisis'          => $call->analisis ? [
                'sentimiento_cliente'          => $call->analisis->sentimiento_cliente,
                'tono_general'                 => $call->analisis->tono_general,
                'intencion_comercial'          => $call->analisis->intencion_comercial,
                'score_venta'                  => $call->analisis->score_venta,
                'objeciones'                   => $call->analisis->objeciones_json ?? [],
                'siguiente_accion'             => $call->analisis->siguiente_accion,
                'analizado_at'                 => $call->analisis->analizado_at?->toIso8601String(),
            ] : null,
        ];
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        // Añadir prefijo si viene sin él (lógica básica para MVP)
        if (strlen($digits) === 9 && str_starts_with($digits, '9')) {
            $digits = '51' . $digits; // Prefijo Perú por defecto
        }
        return '+' . $digits;
    }
}
