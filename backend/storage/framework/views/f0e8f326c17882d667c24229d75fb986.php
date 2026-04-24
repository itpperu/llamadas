<?php $__env->startSection('content'); ?>
<div class="header">
    <h1>Configuración y Diagnóstico</h1>
    <div class="text-muted">Estado del sistema en tiempo real</div>
</div>


<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
    <div class="card" style="text-align: center; padding: 1.25rem;">
        <div class="text-muted" style="font-size: 0.75rem;">Vendedores</div>
        <div style="font-size: 1.75rem; font-weight: 800; color: var(--primary);"><?php echo e($systemStats['total_vendedores']); ?></div>
    </div>
    <div class="card" style="text-align: center; padding: 1.25rem;">
        <div class="text-muted" style="font-size: 0.75rem;">Dispositivos</div>
        <div style="font-size: 1.75rem; font-weight: 800; color: var(--accent);"><?php echo e($systemStats['total_dispositivos']); ?></div>
    </div>
    <div class="card" style="text-align: center; padding: 1.25rem;">
        <div class="text-muted" style="font-size: 0.75rem;">Llamadas</div>
        <div style="font-size: 1.75rem; font-weight: 800; color: #6366f1;"><?php echo e($systemStats['total_llamadas']); ?></div>
    </div>
    <div class="card" style="text-align: center; padding: 1.25rem;">
        <div class="text-muted" style="font-size: 0.75rem;">Analizadas</div>
        <div style="font-size: 1.75rem; font-weight: 800; color: var(--success);"><?php echo e($systemStats['total_analizadas']); ?></div>
    </div>
    <div class="card" style="text-align: center; padding: 1.25rem;">
        <div class="text-muted" style="font-size: 0.75rem;">Audios</div>
        <div style="font-size: 1.75rem; font-weight: 800; color: #f59e0b;"><?php echo e($systemStats['total_audios']); ?></div>
    </div>
    <div class="card" style="text-align: center; padding: 1.25rem;">
        <div class="text-muted" style="font-size: 0.75rem;">Errores</div>
        <div style="font-size: 1.75rem; font-weight: 800; color: <?php echo e($systemStats['total_errores'] > 0 ? 'var(--danger)' : 'var(--text-muted)'); ?>;"><?php echo e($systemStats['total_errores']); ?></div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
    
    <div class="card">
        <h3 style="margin-top:0;">📡 Estado de Servicios</h3>
        <p class="text-muted" style="margin-bottom: 2rem;">Conectividad entre componentes del sistema.</p>
        
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            
            <div style="display:flex; justify-content: space-between; align-items:center; padding: 1rem; background: #f8fafc; border-radius: 8px;">
                <div>
                    <strong>Base de Datos (MySQL)</strong><br>
                    <small class="text-muted">Conexión Docker interna</small>
                </div>
                <span class="badge <?php echo e($dbStatus ? 'badge-success' : 'badge-danger'); ?>" style="padding: 0.5rem 1rem;">
                    <?php echo e($dbStatus ? 'OPERATIVA' : 'CAÍDA'); ?>

                </span>
            </div>

            
            <div style="display:flex; justify-content: space-between; align-items:center; padding: 1rem; background: #f8fafc; border-radius: 8px;">
                <div>
                    <strong>IA (Microservicio Python)</strong><br>
                    <small class="text-muted"><?php echo e($config['ai_url']); ?></small>
                </div>
                <div style="text-align: right;">
                    <span class="badge <?php echo e($aiStatus ? 'badge-success' : 'badge-danger'); ?>" style="padding: 0.5rem 1rem;">
                        <?php echo e($aiStatus ? 'EN LÍNEA' : 'DESCONECTADA'); ?>

                    </span>
                    <?php if($aiPingAt): ?>
                        <br><small class="text-muted">Ping: <?php echo e($aiPingAt->format('H:i:s')); ?></small>
                    <?php endif; ?>
                </div>
            </div>

            
            <div style="display:flex; justify-content: space-between; align-items:center; padding: 1rem; background: #f8fafc; border-radius: 8px;">
                <div>
                    <strong>Worker de Tareas</strong><br>
                    <small class="text-muted">Driver: <?php echo e($config['queue_driver']); ?></small>
                </div>
                <span class="badge <?php echo e($config['queue_driver'] == 'database' ? 'badge-info' : 'badge-warning'); ?>" style="padding: 0.5rem 1rem;">
                    <?php echo e(strtoupper($config['queue_driver'])); ?>

                </span>
            </div>
        </div>
    </div>

    
    <div class="card">
        <h3 style="margin-top:0;">⚙️ Monitoreo de Cola</h3>
        <p class="text-muted" style="margin-bottom: 2rem;">Jobs de procesamiento IA en tiempo real.</p>

        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            
            <div style="display:flex; justify-content: space-between; align-items:center; padding: 1rem; background: <?php echo e($queueStats['health'] === 'critical' ? '#fef2f2' : ($queueStats['health'] === 'warning' ? '#fffbeb' : '#f0fdf4')); ?>; border-radius: 8px; border: 1px solid <?php echo e($queueStats['health'] === 'critical' ? '#fca5a5' : ($queueStats['health'] === 'warning' ? '#fde68a' : '#bbf7d0')); ?>;">
                <div>
                    <strong>Jobs Pendientes</strong><br>
                    <?php if($queueStats['oldest_pending']): ?>
                        <small class="text-muted">Más antiguo: <?php echo e($queueStats['oldest_pending']); ?></small>
                    <?php else: ?>
                        <small class="text-muted">Cola vacía</small>
                    <?php endif; ?>
                </div>
                <div style="font-size: 1.75rem; font-weight: 800; color: <?php echo e($queueStats['pending'] > 0 ? '#f59e0b' : '#22c55e'); ?>;">
                    <?php echo e($queueStats['pending']); ?>

                </div>
            </div>

            
            <div style="display:flex; justify-content: space-between; align-items:center; padding: 1rem; background: <?php echo e($queueStats['failed'] > 0 ? '#fef2f2' : '#f8fafc'); ?>; border-radius: 8px; border: 1px solid <?php echo e($queueStats['failed'] > 0 ? '#fca5a5' : '#e5e7eb'); ?>;">
                <div>
                    <strong>Jobs Fallidos</strong><br>
                    <small class="text-muted">Total acumulado</small>
                </div>
                <div style="font-size: 1.75rem; font-weight: 800; color: <?php echo e($queueStats['failed'] > 0 ? 'var(--danger)' : 'var(--text-muted)'); ?>;">
                    <?php echo e($queueStats['failed']); ?>

                </div>
            </div>

            
            <div style="padding: 0.75rem 1rem; border-radius: 8px; text-align: center; font-weight: 700; font-size: 0.9rem;
                background: <?php echo e($queueStats['health'] === 'healthy' ? '#dcfce7' : ($queueStats['health'] === 'warning' ? '#fef9c3' : '#fee2e2')); ?>;
                color: <?php echo e($queueStats['health'] === 'healthy' ? '#166534' : ($queueStats['health'] === 'warning' ? '#854d0e' : '#991b1b')); ?>;">
                <?php if($queueStats['health'] === 'healthy'): ?>
                    🟢 Cola saludable — Sin acumulación
                <?php elseif($queueStats['health'] === 'warning'): ?>
                    🟡 Procesamiento lento — <?php echo e($queueStats['pending']); ?> jobs acumulados
                <?php else: ?>
                    🔴 Cola saturada — Verificar worker
                <?php endif; ?>
            </div>
        </div>

        
        <?php if($queueStats['recent_failed']->isNotEmpty()): ?>
            <div style="margin-top: 1.5rem;">
                <strong class="text-muted" style="font-size: 0.85rem;">Últimos fallos:</strong>
                <div style="margin-top: 0.5rem; display: flex; flex-direction: column; gap: 0.5rem;">
                    <?php $__currentLoopData = $queueStats['recent_failed']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div style="padding: 0.5rem 0.75rem; background: #fef2f2; border-radius: 6px; border-left: 3px solid var(--danger); font-size: 0.8rem;">
                            <strong><?php echo e($fail->job_name); ?></strong> — <span class="text-muted"><?php echo e($fail->failed_at); ?></span><br>
                            <span style="color: #991b1b;"><?php echo e(Str::limit($fail->error, 100)); ?></span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>


<div class="card" style="margin-top: 2rem;">
    <h3 style="margin-top:0;">🔑 Tokens de API Externa</h3>
    <p class="text-muted" style="margin-bottom:1.5rem;">
        Permite a sistemas externos consultar llamadas y análisis vía <code>GET /api/v1/calls</code>.<br>
        El token se muestra <strong>una sola vez</strong> al crearlo. Guárdalo en un lugar seguro.
    </p>

    
    <?php if(session('new_token')): ?>
        <div style="margin-bottom:1.5rem; padding:1.25rem; background:#f0fdf4; border:2px solid #22c55e; border-radius:10px;">
            <div style="font-weight:700; color:#166534; margin-bottom:0.5rem;">✅ Token generado — cópialo ahora, no se volverá a mostrar</div>
            <div style="display:flex; gap:0.75rem; align-items:center;">
                <code id="newTokenValue" style="flex:1; padding:0.75rem; background:#dcfce7; border-radius:8px; font-size:0.9rem; word-break:break-all; color:#14532d;"><?php echo e(session('new_token')); ?></code>
                <button onclick="copyToken()" style="padding:0.6rem 1rem; background:#22c55e; color:white; border:none; border-radius:8px; cursor:pointer; font-weight:600; white-space:nowrap;">Copiar</button>
            </div>
        </div>
    <?php endif; ?>

    
    <form action="<?php echo e(route('settings.api-tokens.create')); ?>" method="POST" class="ajax-form"
          style="display:grid; grid-template-columns: 1fr 200px auto; gap:1rem; align-items:end; margin-bottom:1.5rem;">
        <?php echo csrf_field(); ?>
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

    
    <?php if($apiTokens->isEmpty()): ?>
        <div style="text-align:center; padding:2rem; color:#94a3b8;">Sin tokens creados aún.</div>
    <?php else: ?>
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
                <?php $__currentLoopData = $apiTokens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $token): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:0.75rem; font-weight:600;"><?php echo e($token->nombre); ?></td>
                    <td style="padding:0.75rem;">
                        <?php if(!$token->activo): ?>
                            <span class="badge badge-danger">Revocado</span>
                        <?php elseif($token->expires_at && $token->expires_at->isPast()): ?>
                            <span class="badge badge-warning">Expirado</span>
                        <?php else: ?>
                            <span class="badge badge-success">Activo</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:0.75rem; font-size:0.85rem; color:#6b7280;">
                        <?php echo e($token->last_used_at ? $token->last_used_at->diffForHumans() : 'Nunca'); ?>

                    </td>
                    <td style="padding:0.75rem; font-size:0.85rem; color:#6b7280;">
                        <?php echo e($token->expires_at ? $token->expires_at->format('d/m/Y') : 'Sin vencimiento'); ?>

                    </td>
                    <td style="padding:0.75rem; font-size:0.85rem; color:#6b7280;">
                        <?php echo e($token->created_at->format('d/m/Y H:i')); ?>

                    </td>
                    <td style="padding:0.75rem;">
                        <?php if($token->activo): ?>
                            <form action="<?php echo e(route('settings.api-tokens.revoke', $token->id)); ?>" method="POST"
                                  onsubmit="return confirm('¿Revocar este token? Los sistemas que lo usen perderán acceso de inmediato.')">
                                <?php echo csrf_field(); ?>
                                <button type="submit" style="padding:0.4rem 0.8rem; background:#fee2e2; color:#991b1b; border:none; border-radius:6px; cursor:pointer; font-size:0.8rem; font-weight:600;">
                                    Revocar
                                </button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    <?php endif; ?>

    
    <div style="margin-top:1.5rem; padding:1rem; background:#f8fafc; border-radius:8px; border:1px solid #e5e7eb;">
        <strong style="font-size:0.9rem;">📖 Uso del endpoint</strong>
        <pre style="margin:0.75rem 0 0; font-size:0.82rem; background:#1e293b; color:#e2e8f0; padding:1rem; border-radius:8px; overflow-x:auto; white-space:pre-wrap;">GET <?php echo e(config('app.url')); ?>/api/v1/calls?phone=%2B51912345678&fecha_desde=2026-04-01&fecha_hasta=2026-04-30

Authorization: Bearer callsync_xxxxxxxxxxxxxxxxxxxxxxxxxxxx</pre>
        <div class="text-muted" style="margin-top:0.75rem; font-size:0.8rem;">
            Parámetros: <code>phone</code> o <code>cliente_id</code> (requerido uno) · <code>fecha_desde</code> · <code>fecha_hasta</code> (opcionales)
        </div>
    </div>
</div>


<div class="card" style="margin-top: 2rem;">
    <h3 style="margin-top:0;">🔧 Parámetros Operativos (Solo Lectura)</h3>
    <p class="text-muted" style="margin-bottom: 1.5rem;">Valores definidos en el archivo .env del servidor.</p>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
        <div>
            <label class="text-muted">Nombre del Sistema:</label><br>
            <div style="padding: 0.75rem; background: #f1f5f9; border-radius: 8px; font-weight: 600;"><?php echo e($config['app_name']); ?></div>
        </div>
        <div>
            <label class="text-muted">URL del Backend (API):</label><br>
            <div style="padding: 0.75rem; background: #f1f5f9; border-radius: 8px; font-weight: 600;"><?php echo e($config['app_url']); ?></div>
        </div>
        <div>
            <label class="text-muted">Base URL IA Python:</label><br>
            <div style="padding: 0.75rem; background: #f1f5f9; border-radius: 8px; font-weight: 600;"><?php echo e($config['ai_url']); ?></div>
        </div>
        <div>
            <label class="text-muted">Zona Horaria:</label><br>
            <div style="padding: 0.75rem; background: #f1f5f9; border-radius: 8px; font-weight: 600;"><?php echo e($config['timezone']); ?></div>
        </div>
    </div>

    <?php if($systemStats['ultima_sincronizacion']): ?>
        <div style="margin-top: 1.5rem; padding: 0.75rem 1rem; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; font-size: 0.85rem; color: #166534;">
            <strong>Última sincronización:</strong> <?php echo e(\Carbon\Carbon::parse($systemStats['ultima_sincronizacion'])->format('d/m/Y H:i:s')); ?> (<?php echo e(\Carbon\Carbon::parse($systemStats['ultima_sincronizacion'])->diffForHumans()); ?>)
        </div>
    <?php endif; ?>

    <div style="margin-top: 1.5rem; padding: 1rem; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; font-size: 0.85rem; color: #92400e;">
        <strong>Comandos de mantenimiento:</strong><br>
        <code style="display: block; margin-top: 0.5rem; padding: 0.5rem; background: #fef3c7; border-radius: 4px;">php artisan system:queue-status</code>
        <code style="display: block; margin-top: 0.25rem; padding: 0.5rem; background: #fef3c7; border-radius: 4px;">php artisan system:backup</code>
        <code style="display: block; margin-top: 0.25rem; padding: 0.5rem; background: #fef3c7; border-radius: 4px;">php artisan system:purge --days=90 --dry-run</code>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('extra_js'); ?>
<script>
function copyToken() {
    var text = document.getElementById('newTokenValue').textContent.trim();
    navigator.clipboard.writeText(text).then(function() {
        alert('Token copiado al portapapeles.');
    });
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/settings/index.blade.php ENDPATH**/ ?>