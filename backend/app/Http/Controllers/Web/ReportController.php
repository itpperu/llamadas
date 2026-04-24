<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Llamada;
use App\Models\Vendedor;
use App\Models\Cliente;
use App\Jobs\ProcessCallAIJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Llamada::with(['vendedor', 'cliente', 'audio', 'analisis'])->orderBy('fecha_inicio', 'desc');

        // Filtros
        if ($request->filled('vendedor_id')) {
            $query->where('vendedor_id', $request->vendedor_id);
        }

        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_inicio', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_inicio', '<=', $request->fecha_hasta);
        }

        if ($request->filled('estado')) {
            $query->where('estado_proceso', $request->estado);
        }

        // DataTables maneja la paginación client-side, enviamos todo el set filtrado
        $llamadas = $query->get();
        $vendedores = Vendedor::all();
        $clientes = Cliente::all();

        return view('reports.index', compact('llamadas', 'vendedores', 'clientes'));
    }

    public function show(Llamada $call)
    {
        $call->load(['vendedor', 'cliente', 'audio', 'analisis', 'logs']);
        return view('reports.show', compact('call'));
    }

    public function reprocess(Llamada $call)
    {
        ProcessCallAIJob::dispatch($call);
        return back()->with('success', 'Llamada encolada para re-análisis.');
    }

    public function streamAudio(Llamada $call)
    {
        if (!$call->audio) return abort(404);
        
        $path = $call->audio->storage_path;
        if (!Storage::disk('local')->exists($path)) {
            return abort(404, 'Audio no encontrado en el servidor.');
        }

        return response()->file(Storage::disk('local')->path($path), [
            'Content-Type' => $call->audio->mime_type ?? 'audio/mpeg',
        ]);
    }

    /**
     * Exportar listado de llamadas a CSV (server-side fallback)
     */
    public function export(Request $request)
    {
        $query = Llamada::with(['vendedor', 'cliente', 'analisis'])->orderBy('fecha_inicio', 'desc');

        if ($request->filled('vendedor_id')) {
            $query->where('vendedor_id', $request->vendedor_id);
        }
        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_inicio', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_inicio', '<=', $request->fecha_hasta);
        }
        if ($request->filled('estado')) {
            $query->where('estado_proceso', $request->estado);
        }

        $llamadas = $query->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="reporte_llamadas_' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($llamadas) {
            $file = fopen('php://output', 'w');
            // BOM for Excel UTF-8
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['Fecha', 'Hora', 'Vendedor', 'Teléfono Cliente', 'Nombre Cliente', 'Tipo', 'Duración', 'Estado', 'Sentimiento', 'Score Venta', 'Resumen']);

            foreach ($llamadas as $call) {
                fputcsv($file, [
                    $call->fecha_inicio->format('d/m/Y'),
                    $call->fecha_inicio->format('H:i:s'),
                    $call->vendedor->nombre ?? '-',
                    $call->telefono_cliente_normalizado,
                    $call->cliente->nombre_referencial ?? '-',
                    $call->tipo_llamada,
                    gmdate("H:i:s", $call->duracion_segundos),
                    $call->estado_proceso,
                    $call->analisis->sentimiento_cliente ?? '-',
                    $call->analisis->score_venta ?? '-',
                    $call->summary_text ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exportar paquete ZIP filtrado por número y/o rango de fechas.
     * Incluye: reporte.csv (metadata + transcript + análisis) + audios/
     */
    public function exportPackage(Request $request)
    {
        if (!$request->filled('cliente_id') && !$request->filled('fecha_desde')) {
            return back()->withErrors(['filtro' => 'Debe seleccionar al menos un número de cliente o una fecha de inicio.']);
        }

        $query = Llamada::with(['vendedor', 'cliente', 'audio', 'analisis'])
            ->orderBy('fecha_inicio', 'asc');

        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_inicio', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_inicio', '<=', $request->fecha_hasta);
        }

        $llamadas = $query->get();

        if ($llamadas->isEmpty()) {
            return back()->withErrors(['filtro' => 'No se encontraron llamadas con los filtros seleccionados.']);
        }

        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zipFileName = 'paquete_llamadas_' . now()->format('Y-m-d_His') . '.zip';
        $zipPath = $tempDir . '/' . $zipFileName;

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return back()->withErrors(['filtro' => 'Error interno al crear el archivo ZIP.']);
        }

        // Construir CSV en memoria
        ob_start();
        $fp = fopen('php://output', 'w');
        fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($fp, [
            'ID', 'Fecha', 'Hora', 'Vendedor', 'Teléfono Cliente', 'Nombre Cliente',
            'Tipo', 'Duración (seg)', 'Estado', 'Sentimiento', 'Tono General',
            'Intención Comercial', 'Score Venta', 'Objeciones', 'Siguiente Acción',
            'Transcript', 'Resumen',
        ]);

        foreach ($llamadas as $call) {
            $objeciones = !empty($call->analisis?->objeciones_json)
                ? implode(' | ', $call->analisis->objeciones_json)
                : '-';
            fputcsv($fp, [
                $call->id,
                $call->fecha_inicio->format('d/m/Y'),
                $call->fecha_inicio->format('H:i:s'),
                $call->vendedor->nombre ?? '-',
                $call->telefono_cliente_normalizado,
                $call->cliente->nombre_referencial ?? '-',
                $call->tipo_llamada,
                $call->duracion_segundos,
                $call->estado_proceso,
                $call->analisis->sentimiento_cliente ?? '-',
                $call->analisis->tono_general ?? '-',
                $call->analisis->intencion_comercial ?? '-',
                $call->analisis->score_venta ?? '-',
                $objeciones,
                $call->analisis->siguiente_accion ?? '-',
                $call->transcript_text ?? '-',
                $call->summary_text ?? '-',
            ]);
        }
        fclose($fp);
        $zip->addFromString('reporte.csv', ob_get_clean());

        // Agregar audios
        foreach ($llamadas as $call) {
            if ($call->audio && Storage::disk('local')->exists($call->audio->storage_path)) {
                $audioFullPath = Storage::disk('local')->path($call->audio->storage_path);
                $ext = pathinfo($call->audio->file_name ?? '', PATHINFO_EXTENSION) ?: 'audio';
                $safeNumber = preg_replace('/[^a-zA-Z0-9+]/', '', $call->telefono_cliente_normalizado);
                $audioName = 'audios/'
                    . $call->fecha_inicio->format('Y-m-d_His')
                    . '_' . $safeNumber
                    . '_' . $call->tipo_llamada
                    . '.' . $ext;
                $zip->addFile($audioFullPath, $audioName);
            }
        }

        $zip->close();

        return response()->download($zipPath, $zipFileName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Reporte agregado por vendedor con métricas comerciales
     */
    public function vendorSummary(Request $request)
    {
        // Métricas agregadas por vendedor usando Query Builder
        $vendedores = DB::table('llamadas')
            ->join('vendedores', 'llamadas.vendedor_id', '=', 'vendedores.id')
            ->leftJoin('analisis_llamada', 'llamadas.id', '=', 'analisis_llamada.llamada_id')
            ->select(
                'vendedores.id',
                'vendedores.nombre',
                'vendedores.estado',
                'vendedores.telefono_corporativo',
                DB::raw('COUNT(llamadas.id) as total_llamadas'),
                DB::raw('SUM(CASE WHEN llamadas.tipo_llamada = "saliente" THEN 1 ELSE 0 END) as llamadas_salientes'),
                DB::raw('SUM(CASE WHEN llamadas.tipo_llamada = "entrante" THEN 1 ELSE 0 END) as llamadas_entrantes'),
                DB::raw('SUM(CASE WHEN llamadas.tipo_llamada = "perdida" THEN 1 ELSE 0 END) as llamadas_perdidas'),
                DB::raw('ROUND(AVG(llamadas.duracion_segundos)) as duracion_promedio'),
                DB::raw('SUM(llamadas.duracion_segundos) as duracion_total'),
                DB::raw('ROUND(AVG(analisis_llamada.score_venta), 1) as score_promedio'),
                DB::raw('SUM(CASE WHEN analisis_llamada.sentimiento_cliente = "positivo" THEN 1 ELSE 0 END) as sentimiento_positivo'),
                DB::raw('SUM(CASE WHEN analisis_llamada.sentimiento_cliente = "negativo" THEN 1 ELSE 0 END) as sentimiento_negativo'),
                DB::raw('SUM(CASE WHEN analisis_llamada.sentimiento_cliente = "neutral" THEN 1 ELSE 0 END) as sentimiento_neutral'),
                DB::raw('SUM(CASE WHEN llamadas.estado_proceso = "analizada" THEN 1 ELSE 0 END) as llamadas_analizadas'),
                DB::raw('SUM(CASE WHEN llamadas.estado_proceso = "error" THEN 1 ELSE 0 END) as llamadas_error'),
                DB::raw('MAX(llamadas.fecha_inicio) as ultima_llamada')
            )
            ->groupBy('vendedores.id', 'vendedores.nombre', 'vendedores.estado', 'vendedores.telefono_corporativo')
            ->orderBy('total_llamadas', 'desc')
            ->get();

        // Métricas globales para las tarjetas superiores
        $totales = (object)[
            'total_llamadas' => $vendedores->sum('total_llamadas'),
            'duracion_total' => $vendedores->sum('duracion_total'),
            'score_promedio' => $vendedores->avg('score_promedio'),
            'total_analizadas' => $vendedores->sum('llamadas_analizadas'),
            'total_errores' => $vendedores->sum('llamadas_error'),
            'sentimiento_positivo' => $vendedores->sum('sentimiento_positivo'),
            'sentimiento_negativo' => $vendedores->sum('sentimiento_negativo'),
            'sentimiento_neutral' => $vendedores->sum('sentimiento_neutral'),
        ];

        return view('reports.vendors', compact('vendedores', 'totales'));
    }
}
