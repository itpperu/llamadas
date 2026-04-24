@extends('layouts.app')

@section('content')
<div class="header">
    <h1>Configuración y Diagnóstico</h1>
    <div class="text-muted">Estado del sistema en tiempo real</div>
</div>

{{-- Estadísticas del sistema --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
    <div class="card" style="text-align: center; padding: 1.25rem;">
        <div class="text-muted" style="font-size: 0.75rem;">Vendedores</div>
        <div style="font-size: 1.75rem; font-weight: 800; color: var(--primary);">{{ $systemStats['total_vendedores'] }}</div>
    </div>
    <div class="card" style="text-align: center; padding: 1.25rem;">
        <div class="text-muted" style="font-size: 0.75rem;">Dispositivos</div>
        <div style="font-size: 1.75rem; font-weight: 800; color: var(--accent);">{{ $systemStats['total_dispositivos'] }}</div>
    </div>
    <div class="card" style="text-align: center; padding: 1.25rem;">
        <div class="text-muted" style="font-size: 0.75rem;">Llamadas</div>
        <div style="font-size: 1.75rem; font-weight: 800; color: #6366f1;">{{ $systemStats['total_llamadas'] }}</div>
    </div>
    <div class="card" style="text-align: center; padding: 1.25rem;">
        <div class="text-muted" style="font-size: 0.75rem;">Analizadas</div>
        <div style="font-size: 1.75rem; font-weight: 800; color: var(--success);">{{ $systemStats['total_analizadas'] }}</div>
    </div>
    <div class="card" style="text-align: center; padding: 1.25rem;">
        <div class="text-muted" style="font-size: 0.75rem;">Audios</div>
        <div style="font-size: 1.75rem; font-weight: 800; color: #f59e0b;">{{ $systemStats['total_audios'] }}</div>
    </div>
    <div class="card" style="text-align: center; padding: 1.25rem;">
        <div class="text-muted" style="font-size: 0.75rem;">Errores</div>
        <div style="font-size: 1.75rem; font-weight: 800; color: {{ $systemStats['total_errores'] > 0 ? 'var(--danger)' : 'var(--text-muted)' }};">{{ $systemStats['total_errores'] }}</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
    {{-- Estado de Servicios --}}
    <div class="card">
        <h3 style="margin-top:0;">📡 Estado de Servicios</h3>
        <p class="text-muted" style="margin-bottom: 2rem;">Conectividad entre componentes del sistema.</p>
        
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            {{-- DB --}}
            <div style="display:flex; justify-content: space-between; align-items:center; padding: 1rem; background: #f8fafc; border-radius: 8px;">
                <div>
                    <strong>Base de Datos (MySQL)</strong><br>
                    <small class="text-muted">Conexión Docker interna</small>
                </div>
                <span class="badge {{ $dbStatus ? 'badge-success' : 'badge-danger' }}" style="padding: 0.5rem 1rem;">
                    {{ $dbStatus ? 'OPERATIVA' : 'CAÍDA' }}
                </span>
            </div>

            {{-- AI --}}
            <div style="display:flex; justify-content: space-between; align-items:center; padding: 1rem; background: #f8fafc; border-radius: 8px;">
                <div>
                    <strong>IA (Microservicio Python)</strong><br>
                    <small class="text-muted">{{ $config['ai_url'] }}</small>
                </div>
                <div style="text-align: right;">
                    <span class="badge {{ $aiStatus ? 'badge-success' : 'badge-danger' }}" style="padding: 0.5rem 1rem;">
                        {{ $aiStatus ? 'EN LÍNEA' : 'DESCONECTADA' }}
                    </span>
                    @if($aiPingAt)
                        <br><small class="text-muted">Ping: {{ $aiPingAt->format('H:i:s') }}</small>
                    @endif
                </div>
            </div>

            {{-- Worker --}}
            <div style="display:flex; justify-content: space-between; align-items:center; padding: 1rem; background: #f8fafc; border-radius: 8px;">
                <div>
                    <strong>Worker de Tareas</strong><br>
                    <small class="text-muted">Driver: {{ $config['queue_driver'] }}</small>
                </div>
                <span class="badge {{ $config['queue_driver'] == 'database' ? 'badge-info' : 'badge-warning' }}" style="padding: 0.5rem 1rem;">
                    {{ strtoupper($config['queue_driver']) }}
                </span>
            </div>
        </div>
    </div>

    {{-- Monitoreo de Cola --}}
    <div class="card">
        <h3 style="margin-top:0;">⚙️ Monitoreo de Cola</h3>
        <p class="text-muted" style="margin-bottom: 2rem;">Jobs de procesamiento IA en tiempo real.</p>

        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            {{-- Pendientes --}}
            <div style="display:flex; justify-content: space-between; align-items:center; padding: 1rem; background: {{ $queueStats['health'] === 'critical' ? '#fef2f2' : ($queueStats['health'] === 'warning' ? '#fffbeb' : '#f0fdf4') }}; border-radius: 8px; border: 1px solid {{ $queueStats['health'] === 'critical' ? '#fca5a5' : ($queueStats['health'] === 'warning' ? '#fde68a' : '#bbf7d0') }};">
                <div>
                    <strong>Jobs Pendientes</strong><br>
                    @if($queueStats['oldest_pending'])
                        <small class="text-muted">Más antiguo: {{ $queueStats['oldest_pending'] }}</small>
                    @else
                        <small class="text-muted">Cola vacía</small>
                    @endif
                </div>
                <div style="font-size: 1.75rem; font-weight: 800; color: {{ $queueStats['pending'] > 0 ? '#f59e0b' : '#22c55e' }};">
                    {{ $queueStats['pending'] }}
                </div>
            </div>

            {{-- Fallidos --}}
            <div style="display:flex; justify-content: space-between; align-items:center; padding: 1rem; background: {{ $queueStats['failed'] > 0 ? '#fef2f2' : '#f8fafc' }}; border-radius: 8px; border: 1px solid {{ $queueStats['failed'] > 0 ? '#fca5a5' : '#e5e7eb' }};">
                <div>
                    <strong>Jobs Fallidos</strong><br>
                    <small class="text-muted">Total acumulado</small>
                </div>
                <div style="font-size: 1.75rem; font-weight: 800; color: {{ $queueStats['failed'] > 0 ? 'var(--danger)' : 'var(--text-muted)' }};">
                    {{ $queueStats['failed'] }}
                </div>
            </div>

            {{-- Estado de salud --}}
            <div style="padding: 0.75rem 1rem; border-radius: 8px; text-align: center; font-weight: 700; font-size: 0.9rem;
                background: {{ $queueStats['health'] === 'healthy' ? '#dcfce7' : ($queueStats['health'] === 'warning' ? '#fef9c3' : '#fee2e2') }};
                color: {{ $queueStats['health'] === 'healthy' ? '#166534' : ($queueStats['health'] === 'warning' ? '#854d0e' : '#991b1b') }};">
                @if($queueStats['health'] === 'healthy')
                    🟢 Cola saludable — Sin acumulación
                @elseif($queueStats['health'] === 'warning')
                    🟡 Procesamiento lento — {{ $queueStats['pending'] }} jobs acumulados
                @else
                    🔴 Cola saturada — Verificar worker
                @endif
            </div>
        </div>

        {{-- Últimos fallos --}}
        @if($queueStats['recent_failed']->isNotEmpty())
            <div style="margin-top: 1.5rem;">
                <strong class="text-muted" style="font-size: 0.85rem;">Últimos fallos:</strong>
                <div style="margin-top: 0.5rem; display: flex; flex-direction: column; gap: 0.5rem;">
                    @foreach($queueStats['recent_failed'] as $fail)
                        <div style="padding: 0.5rem 0.75rem; background: #fef2f2; border-radius: 6px; border-left: 3px solid var(--danger); font-size: 0.8rem;">
                            <strong>{{ $fail->job_name }}</strong> — <span class="text-muted">{{ $fail->failed_at }}</span><br>
                            <span style="color: #991b1b;">{{ Str::limit($fail->error, 100) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Tokens de API Externa --}}
<div class="card" style="margin-top: 2rem;">
    <h3 style="margin-top:0;">🔑 Tokens de API Externa</h3>
    <p class="text-muted" style="margin-bottom:1.5rem;">
        Permite a sistemas externos consultar llamadas y análisis vía <code>GET /api/v1/calls</code>.<br>
        El token se muestra <strong>una sola vez</strong> al crearlo. Guárdalo en un lugar seguro.
    </p>

    {{-- Mostrar token recién creado --}}
    @if(session('new_token'))
        <div style="margin-bottom:1.5rem; padding:1.25rem; background:#f0fdf4; border:2px solid #22c55e; border-radius:10px;">
            <div style="font-weight:700; color:#166534; margin-bottom:0.5rem;">✅ Token generado — cópialo ahora, no se volverá a mostrar</div>
            <div style="display:flex; gap:0.75rem; align-items:center;">
                <code id="newTokenValue" style="flex:1; padding:0.75rem; background:#dcfce7; border-radius:8px; font-size:0.9rem; word-break:break-all; color:#14532d;">{{ session('new_token') }}</code>
                <button onclick="copyToken()" style="padding:0.6rem 1rem; background:#22c55e; color:white; border:none; border-radius:8px; cursor:pointer; font-weight:600; white-space:nowrap;">Copiar</button>
            </div>
        </div>
    @endif

    {{-- Crear nuevo token --}}
    <form action="{{ route('settings.api-tokens.create') }}" method="POST" class="ajax-form"
          style="display:grid; grid-template-columns: 1fr 200px auto; gap:1rem; align-items:end; margin-bottom:1.5rem;">
        @csrf
        <div>
            <label class="text-muted">Nombre / Descripción del token</label><br>
            <input type="text" name="nombre" required placeholder="Ej: Integración CRM, Dashboard BI..."
                   style="width:100%; height:40px; border-radius:8px; border:1px solid #ddd; padding:0.5rem; box-sizing:border-box; font-family:inherit;">
        </div>
        <div>
            <label class="text-muted">Vence el (opcional)</label><br>
            <input type="date" name="expires_at"
                   style="width:100%; height:40px; border-radius:8px; border:1px solid #ddd; padding:0.5rem; box-sizing:border-box;">
        </div>
        <div>
            <button type="submit" class="btn btn-primary" data-loader-text="Generando...">+ Generar Token</button>
        </div>
    </form>

    {{-- Listado de tokens --}}
    @if($apiTokens->isEmpty())
        <div style="text-align:center; padding:2rem; color:#94a3b8;">Sin tokens creados aún.</div>
    @else
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:2px solid #e5e7eb;">
                    <th style="text-align:left; padding:0.75rem; font-size:0.8rem; color:#6b7280; text-transform:uppercase;">Nombre</th>
                    <th style="text-align:left; padding:0.75rem; font-size:0.8rem; color:#6b7280; text-transform:uppercase;">Estado</th>
                    <th style="text-align:left; padding:0.75rem; font-size:0.8rem; color:#6b7280; text-transform:uppercase;">Último uso</th>
                    <th style="text-align:left; padding:0.75rem; font-size:0.8rem; color:#6b7280; text-transform:uppercase;">Vence</th>
                    <th style="text-align:left; padding:0.75rem; font-size:0.8rem; color:#6b7280; text-transform:uppercase;">Creado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($apiTokens as $token)
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:0.75rem; font-weight:600;">{{ $token->nombre }}</td>
                    <td style="padding:0.75rem;">
                        @if(!$token->activo)
                            <span class="badge badge-danger">Revocado</span>
                        @elseif($token->expires_at && $token->expires_at->isPast())
                            <span class="badge badge-warning">Expirado</span>
                        @else
                            <span class="badge badge-success">Activo</span>
                        @endif
                    </td>
                    <td style="padding:0.75rem; font-size:0.85rem; color:#6b7280;">
                        {{ $token->last_used_at ? $token->last_used_at->diffForHumans() : 'Nunca' }}
                    </td>
                    <td style="padding:0.75rem; font-size:0.85rem; color:#6b7280;">
                        {{ $token->expires_at ? $token->expires_at->format('d/m/Y') : 'Sin vencimiento' }}
                    </td>
                    <td style="padding:0.75rem; font-size:0.85rem; color:#6b7280;">
                        {{ $token->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td style="padding:0.75rem;">
                        @if($token->activo)
                            <form action="{{ route('settings.api-tokens.revoke', $token->id) }}" method="POST"
                                  onsubmit="return confirm('¿Revocar este token? Los sistemas que lo usen perderán acceso de inmediato.')">
                                @csrf
                                <button type="submit" style="padding:0.4rem 0.8rem; background:#fee2e2; color:#991b1b; border:none; border-radius:6px; cursor:pointer; font-size:0.8rem; font-weight:600;">
                                    Revocar
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Documentación del endpoint --}}
    <div style="margin-top:1.5rem; padding:1rem; background:#f8fafc; border-radius:8px; border:1px solid #e5e7eb;">
        <strong style="font-size:0.9rem;">📖 Uso del endpoint</strong>
        <pre style="margin:0.75rem 0 0; font-size:0.82rem; background:#1e293b; color:#e2e8f0; padding:1rem; border-radius:8px; overflow-x:auto; white-space:pre-wrap;">GET {{ config('app.url') }}/api/v1/calls?phone=%2B51912345678&fecha_desde=2026-04-01&fecha_hasta=2026-04-30

Authorization: Bearer callsync_xxxxxxxxxxxxxxxxxxxxxxxxxxxx</pre>
        <div class="text-muted" style="margin-top:0.75rem; font-size:0.8rem;">
            Parámetros: <code>phone</code> o <code>cliente_id</code> (requerido uno) · <code>fecha_desde</code> · <code>fecha_hasta</code> (opcionales)
        </div>
    </div>
</div>

{{-- Parámetros Operativos --}}
<div class="card" style="margin-top: 2rem;">
    <h3 style="margin-top:0;">🔧 Parámetros Operativos (Solo Lectura)</h3>
    <p class="text-muted" style="margin-bottom: 1.5rem;">Valores definidos en el archivo .env del servidor.</p>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
        <div>
            <label class="text-muted">Nombre del Sistema:</label><br>
            <div style="padding: 0.75rem; background: #f1f5f9; border-radius: 8px; font-weight: 600;">{{ $config['app_name'] }}</div>
        </div>
        <div>
            <label class="text-muted">URL del Backend (API):</label><br>
            <div style="padding: 0.75rem; background: #f1f5f9; border-radius: 8px; font-weight: 600;">{{ $config['app_url'] }}</div>
        </div>
        <div>
            <label class="text-muted">Base URL IA Python:</label><br>
            <div style="padding: 0.75rem; background: #f1f5f9; border-radius: 8px; font-weight: 600;">{{ $config['ai_url'] }}</div>
        </div>
        <div>
            <label class="text-muted">Zona Horaria:</label><br>
            <div style="padding: 0.75rem; background: #f1f5f9; border-radius: 8px; font-weight: 600;">{{ $config['timezone'] }}</div>
        </div>
    </div>

    @if($systemStats['ultima_sincronizacion'])
        <div style="margin-top: 1.5rem; padding: 0.75rem 1rem; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; font-size: 0.85rem; color: #166534;">
            <strong>Última sincronización:</strong> {{ \Carbon\Carbon::parse($systemStats['ultima_sincronizacion'])->format('d/m/Y H:i:s') }} ({{ \Carbon\Carbon::parse($systemStats['ultima_sincronizacion'])->diffForHumans() }})
        </div>
    @endif

    <div style="margin-top: 1.5rem; padding: 1rem; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; font-size: 0.85rem; color: #92400e;">
        <strong>Comandos de mantenimiento:</strong><br>
        <code style="display: block; margin-top: 0.5rem; padding: 0.5rem; background: #fef3c7; border-radius: 4px;">php artisan system:queue-status</code>
        <code style="display: block; margin-top: 0.25rem; padding: 0.5rem; background: #fef3c7; border-radius: 4px;">php artisan system:backup</code>
        <code style="display: block; margin-top: 0.25rem; padding: 0.5rem; background: #fef3c7; border-radius: 4px;">php artisan system:purge --days=90 --dry-run</code>
    </div>
</div>
@endsection

@section('extra_js')
<script>
function copyToken() {
    var text = document.getElementById('newTokenValue').textContent.trim();
    navigator.clipboard.writeText(text).then(function() {
        alert('Token copiado al portapapeles.');
    });
}
</script>
@endsection
