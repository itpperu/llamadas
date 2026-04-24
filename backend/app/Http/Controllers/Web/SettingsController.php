<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SettingsController extends Controller
{
    public function index()
    {
        // 1. Verificar Estado de la Base de Datos
        $dbStatus = true;
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $dbStatus = false;
        }

        // 2. Verificar Microservicio IA (Python)
        $aiUrl = config('services.ai_worker.url', env('AI_WORKER_URL'));
        $aiStatus = false;
        $aiPingAt = null;

        try {
            $baseUrl = str_replace('/process-call', '/', $aiUrl);
            $response = Http::timeout(3)->get($baseUrl);
            if ($response->successful()) {
                $aiStatus = true;
                $aiPingAt = now();
            }
        } catch (\Exception $e) {
            Log::error("AIPing failed: " . $e->getMessage());
        }

        // 3. Obtener Configuración Actual (Safe)
        $config = [
            'app_name' => config('app.name'),
            'app_url' => config('app.url'),
            'ai_url' => $aiUrl,
            'queue_driver' => config('queue.default'),
            'timezone' => config('app.timezone'),
        ];

        // 4. Estado de la Cola de Trabajos
        $queueStats = $this->getQueueStats();

        // 5. Estadísticas generales del sistema
        $systemStats = $this->getSystemStats();

        $apiTokens = ApiToken::orderBy('created_at', 'desc')->get();

        return view('settings.index', compact('dbStatus', 'aiStatus', 'aiPingAt', 'config', 'queueStats', 'systemStats', 'apiTokens'));
    }

    public function createApiToken(Request $request)
    {
        $request->validate([
            'nombre'     => ['required', 'string', 'max:100'],
            'expires_at' => ['nullable', 'date', 'after:today'],
        ]);

        $plain = 'callsync_' . Str::random(40);
        $hash  = hash('sha256', $plain);

        ApiToken::create([
            'nombre'     => $request->nombre,
            'token_hash' => $hash,
            'activo'     => true,
            'expires_at' => $request->expires_at ?: null,
        ]);

        return redirect()->route('settings.index')
            ->with('new_token', $plain)
            ->with('success', 'Token creado. Cópialo ahora — no se volverá a mostrar.');
    }

    public function revokeApiToken(ApiToken $apiToken)
    {
        $apiToken->update(['activo' => false]);
        return redirect()->route('settings.index')->with('success', 'Token revocado correctamente.');
    }

    private function getQueueStats(): array
    {
        $pending = DB::table('jobs')->count();
        $failed = DB::table('failed_jobs')->count();
        $pendingOldest = DB::table('jobs')->min('created_at');

        $recentFailed = DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->limit(5)
            ->get(['id', 'payload', 'exception', 'failed_at'])
            ->map(function ($f) {
                $payload = json_decode($f->payload, true);
                $exceptionLines = explode("\n", $f->exception);
                return (object) [
                    'id' => $f->id,
                    'job_name' => class_basename($payload['displayName'] ?? 'Desconocido'),
                    'error' => substr($exceptionLines[0] ?? '', 0, 150),
                    'failed_at' => $f->failed_at,
                ];
            });

        return [
            'pending' => $pending,
            'failed' => $failed,
            'oldest_pending' => $pendingOldest ? Carbon::parse($pendingOldest)->diffForHumans() : null,
            'recent_failed' => $recentFailed,
            'health' => $pending > 50 ? 'critical' : ($pending > 10 ? 'warning' : 'healthy'),
        ];
    }

    private function getSystemStats(): array
    {
        return [
            'total_vendedores' => DB::table('vendedores')->count(),
            'total_dispositivos' => DB::table('dispositivos')->count(),
            'total_llamadas' => DB::table('llamadas')->count(),
            'total_analizadas' => DB::table('llamadas')->where('estado_proceso', 'analizada')->count(),
            'total_audios' => DB::table('audios_llamada')->count(),
            'total_errores' => DB::table('llamadas')->where('estado_proceso', 'error')->count(),
            'total_logs' => DB::table('logs_sincronizacion')->count(),
            'ultima_sincronizacion' => DB::table('logs_sincronizacion')->max('created_at'),
        ];
    }
}
