<?php $__env->startSection('content'); ?>
<div class="header">
    <div>
        <h1 class="page-header-title">Llamadas Comerciales</h1>
        <div class="page-header-sub">Registro y análisis de interacciones con clientes</div>
    </div>
    <div style="display:flex; gap:0.75rem; align-items:center;">
        <div style="background:#f1f5f9; border-radius:99px; padding:0.35rem 0.9rem; font-size:0.82rem; font-weight:600; color:#475569;">
            <?php echo e($llamadas->count()); ?> <?php echo e($llamadas->count() === 1 ? 'registro' : 'registros'); ?>

        </div>
    </div>
</div>

<div class="card">
    <form action="<?php echo e(route('reports.index')); ?>" method="GET" class="ajax-form" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; align-items: end;">
        <div>
            <label class="text-muted">Vendedor</label><br>
            <select name="vendedor_id" style="width:100%; height:40px; border-radius:8px; border:1px solid #ddd; padding: 0.5rem;">
                <option value="">Todos</option>
                <?php $__currentLoopData = $vendedores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($v->id); ?>" <?php echo e(request('vendedor_id') == $v->id ? 'selected' : ''); ?>><?php echo e($v->nombre); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div>
            <label class="text-muted">Cliente</label><br>
            <select name="cliente_id" style="width:100%; height:40px; border-radius:8px; border:1px solid #ddd; padding: 0.5rem;">
                <option value="">Todos</option>
                <?php $__currentLoopData = $clientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($c->id); ?>" <?php echo e(request('cliente_id') == $c->id ? 'selected' : ''); ?>><?php echo e($c->telefono_normalizado); ?> (<?php echo e($c->nombre_referencial); ?>)</option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div>
            <label class="text-muted">Desde</label><br>
            <input type="date" name="fecha_desde" value="<?php echo e(request('fecha_desde')); ?>" style="width:100%; height:40px; border-radius:8px; border:1px solid #ddd; padding: 0.5rem;">
        </div>
        <div>
            <label class="text-muted">Hasta</label><br>
            <input type="date" name="fecha_hasta" value="<?php echo e(request('fecha_hasta')); ?>" style="width:100%; height:40px; border-radius:8px; border:1px solid #ddd; padding: 0.5rem;">
        </div>
        <div>
            <label class="text-muted">Estado</label><br>
            <select name="estado" style="width:100%; height:40px; border-radius:8px; border:1px solid #ddd; padding: 0.5rem;">
                <option value="">Cualquiera</option>
                <option value="registrada" <?php echo e(request('estado') == 'registrada' ? 'selected' : ''); ?>>Registrada</option>
                <option value="audio_pendiente" <?php echo e(request('estado') == 'audio_pendiente' ? 'selected' : ''); ?>>Audio Pendiente</option>
                <option value="audio_subido" <?php echo e(request('estado') == 'audio_subido' ? 'selected' : ''); ?>>Audio Subido</option>
                <option value="analizada" <?php echo e(request('estado') == 'analizada' ? 'selected' : ''); ?>>Analizada</option>
                <option value="error" <?php echo e(request('estado') == 'error' ? 'selected' : ''); ?>>Error</option>
            </select>
        </div>
        <div>
            <button type="submit" class="btn btn-primary" data-loader-text="Filtrando..." style="width: 100%; justify-content: center;">🔍 Filtrar</button>
        </div>
        <?php if(request()->anyFilled(['vendedor_id', 'cliente_id', 'fecha_desde', 'fecha_hasta', 'estado'])): ?>
            <div style="grid-column: span 1;">
                <a href="<?php echo e(route('reports.index')); ?>" class="btn" style="background-color: #eee; width:100%; justify-content: center;">Limpiar</a>
            </div>
        <?php endif; ?>
    </form>
</div>


<div class="card" style="border-left: 4px solid var(--accent);">
    <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1rem;">
        <span style="font-size:1.4rem;">📦</span>
        <div>
            <strong>Exportar Paquete ZIP</strong>
            <div class="text-muted">Descarga metadata, transcripts, análisis y audios. Filtra por número y/o rango de fechas.</div>
        </div>
    </div>
    <form action="<?php echo e(route('reports.export-package')); ?>" method="GET" id="exportPackageForm"
          style="display:grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap:1rem; align-items:end;">
        <div>
            <label class="text-muted">Número / Cliente</label><br>
            <select name="cliente_id" style="width:100%; height:40px; border-radius:8px; border:1px solid #ddd; padding:0.5rem;">
                <option value="">Todos los números</option>
                <?php $__currentLoopData = $clientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($c->id); ?>"><?php echo e($c->telefono_normalizado); ?> (<?php echo e($c->nombre_referencial); ?>)</option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div>
            <label class="text-muted">Desde</label><br>
            <input type="date" name="fecha_desde" style="width:100%; height:40px; border-radius:8px; border:1px solid #ddd; padding:0.5rem;">
        </div>
        <div>
            <label class="text-muted">Hasta</label><br>
            <input type="date" name="fecha_hasta" style="width:100%; height:40px; border-radius:8px; border:1px solid #ddd; padding:0.5rem;">
        </div>
        <div>
            <button type="submit" id="btnExportPackage" class="btn btn-success" style="width:100%; justify-content:center;">
                📦 Descargar ZIP
            </button>
        </div>
    </form>
    <div class="text-muted" style="margin-top:0.75rem; font-size:0.8rem;">
        Requiere al menos un número de cliente o una fecha de inicio. El ZIP incluye un CSV completo y los archivos de audio disponibles.
    </div>
</div>

<div class="card" style="padding: 1rem; overflow: hidden;">
    <table id="reportesTable" class="display" style="width:100%">
        <thead>
            <tr>
                <th>Fecha/Hora</th>
                <th>Vendedor</th>
                <th>Cliente</th>
                <th>Tipo</th>
                <th>Duración</th>
                <th>Estado</th>
                <th>Análisis</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $llamadas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $call): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td style="font-weight: 600;" data-order="<?php echo e($call->fecha_inicio->format('Y-m-d H:i:s')); ?>">
                        <?php echo e($call->fecha_inicio->format('d/m/Y')); ?><br>
                        <span class="text-muted" style="font-weight: 400;"><?php echo e($call->fecha_inicio->format('H:i')); ?></span>
                    </td>
                    <td><?php echo e($call->vendedor->nombre); ?></td>
                    <td>
                        <?php echo e($call->telefono_cliente_normalizado); ?><br>
                        <span class="text-muted"><?php echo e($call->cliente->nombre_referencial ?? ''); ?></span>
                    </td>
                    <td>
                        <span class="badge" style="background-color: <?php echo e($call->tipo_llamada == 'saliente' ? '#e0f2fe' : ($call->tipo_llamada == 'perdida' ? '#fef9c3' : '#f0fdf4')); ?>; color: <?php echo e($call->tipo_llamada == 'saliente' ? '#0369a1' : ($call->tipo_llamada == 'perdida' ? '#854d0e' : '#15803d')); ?>">
                            <?php echo e($call->tipo_llamada); ?>

                        </span>
                    </td>
                    <td><?php echo e(gmdate("H:i:s", $call->duracion_segundos)); ?></td>
                    <td>
                        <?php
                            $badgeClass = match($call->estado_proceso) {
                                'analizada' => 'badge-success',
                                'error' => 'badge-danger',
                                'audio_pendiente' => 'badge-warning',
                                default => 'badge-info'
                            };
                        ?>
                        <span class="badge <?php echo e($badgeClass); ?>"><?php echo e(str_replace('_', ' ', $call->estado_proceso)); ?></span>
                    </td>
                    <td>
                        <?php if($call->analisis): ?>
                            <div style="display:flex; align-items:center; gap:0.5rem;">
                                <div style="width: 30px; height: 30px; border-radius: 50%; background: <?php echo e($call->analisis->sentimiento_cliente == 'positivo' ? '#dcfce7' : 
                                    ($call->analisis->sentimiento_cliente == 'negativo' ? '#fee2e2' : '#f1f5f9')); ?>; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                                    <?php echo e($call->analisis->sentimiento_cliente == 'positivo' ? '😊' : 
                                        ($call->analisis->sentimiento_cliente == 'negativo' ? '😠' : '😐')); ?>

                                </div>
                                <span style="font-weight: 700; color: #4338ca;"><?php echo e($call->analisis->score_venta); ?>%</span>
                            </div>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?php echo e(route('reports.show', $call->id)); ?>" class="btn" style="background: #f1f5f9; padding: 0.5rem 0.75rem;">Ver</a>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('extra_js'); ?>
<script>
$(document).ready(function() {
    $('#reportesTable').DataTable({
        dom: '<"top"Bfl>rt<"bottom"ip>',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '📥 Exportar Excel',
                title: 'Reporte de Llamadas - <?php echo e(now()->format("d-m-Y")); ?>',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5]
                }
            }
        ],
        language: {
            search: "Buscar:",
            lengthMenu: "Mostrar _MENU_ registros",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Sin registros",
            infoFiltered: "(filtrado de _MAX_ totales)",
            zeroRecords: "No se encontraron resultados",
            paginate: {
                first: "Primero",
                last: "Último",
                next: "→",
                previous: "←"
            }
        },
        order: [[0, 'desc']],
        pageLength: 15,
        responsive: true
    });

    // Loader para exportación de paquete ZIP (descarga de archivo, la página no navega)
    $('#exportPackageForm').on('submit', function() {
        var $btn = $('#btnExportPackage');
        $btn.prop('disabled', true).addClass('is-loading');
        showLoader('Preparando paquete ZIP...');
        // Ocultar loader cuando el navegador recupera el foco tras el diálogo de descarga
        // o después de 10 segundos como máximo
        var timer = setTimeout(function() { hideLoader(); $btn.prop('disabled', false).removeClass('is-loading'); }, 10000);
        $(window).one('focus', function() {
            clearTimeout(timer);
            setTimeout(function() { hideLoader(); $btn.prop('disabled', false).removeClass('is-loading'); }, 800);
        });
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/reports/index.blade.php ENDPATH**/ ?>