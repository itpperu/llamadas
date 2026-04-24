<?php $__env->startSection('content'); ?>
<div class="header">
    <h1>Resumen por Vendedor</h1>
    <div class="text-muted">Métricas agregadas de rendimiento comercial</div>
</div>


<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
    <div class="card" style="text-align: center; border-top: 4px solid var(--primary);">
        <div class="text-muted" style="margin-bottom: 0.5rem;">Total Llamadas</div>
        <div style="font-size: 2.25rem; font-weight: 800; color: var(--primary);"><?php echo e($totales->total_llamadas); ?></div>
    </div>
    <div class="card" style="text-align: center; border-top: 4px solid var(--accent);">
        <div class="text-muted" style="margin-bottom: 0.5rem;">Tiempo Total</div>
        <div style="font-size: 2.25rem; font-weight: 800; color: var(--accent);"><?php echo e(gmdate("H:i:s", $totales->duracion_total ?? 0)); ?></div>
        <div class="text-muted" style="font-size: 0.75rem;">horas : min : seg</div>
    </div>
    <div class="card" style="text-align: center; border-top: 4px solid #f59e0b;">
        <div class="text-muted" style="margin-bottom: 0.5rem;">Score Promedio</div>
        <div style="font-size: 2.25rem; font-weight: 800; color: #f59e0b;"><?php echo e(number_format($totales->score_promedio ?? 0, 1)); ?>%</div>
    </div>
    <div class="card" style="text-align: center; border-top: 4px solid var(--success);">
        <div class="text-muted" style="margin-bottom: 0.5rem;">Analizadas</div>
        <div style="font-size: 2.25rem; font-weight: 800; color: var(--success);"><?php echo e($totales->total_analizadas); ?></div>
    </div>
</div>


<div class="card" style="margin-bottom: 2rem;">
    <h3 style="margin-top:0; margin-bottom: 1.25rem;">Distribución de Sentimiento Global</h3>
    <?php
        $totalSent = max(1, $totales->sentimiento_positivo + $totales->sentimiento_negativo + $totales->sentimiento_neutral);
        $pctPos = round(($totales->sentimiento_positivo / $totalSent) * 100);
        $pctNeg = round(($totales->sentimiento_negativo / $totalSent) * 100);
        $pctNeu = 100 - $pctPos - $pctNeg;
    ?>
    <div style="display: flex; height: 32px; border-radius: 16px; overflow: hidden; background: #f1f5f9;">
        <?php if($pctPos > 0): ?>
        <div style="width: <?php echo e($pctPos); ?>%; background: linear-gradient(135deg, #22c55e, #16a34a); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 0.8rem; min-width: 40px;">
            😊 <?php echo e($pctPos); ?>%
        </div>
        <?php endif; ?>
        <?php if($pctNeu > 0): ?>
        <div style="width: <?php echo e($pctNeu); ?>%; background: linear-gradient(135deg, #94a3b8, #64748b); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 0.8rem; min-width: 40px;">
            😐 <?php echo e($pctNeu); ?>%
        </div>
        <?php endif; ?>
        <?php if($pctNeg > 0): ?>
        <div style="width: <?php echo e($pctNeg); ?>%; background: linear-gradient(135deg, #ef4444, #dc2626); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 0.8rem; min-width: 40px;">
            😠 <?php echo e($pctNeg); ?>%
        </div>
        <?php endif; ?>
    </div>
    <div style="display: flex; justify-content: space-between; margin-top: 0.75rem;">
        <span class="text-muted">😊 Positivo: <strong><?php echo e($totales->sentimiento_positivo); ?></strong></span>
        <span class="text-muted">😐 Neutral: <strong><?php echo e($totales->sentimiento_neutral); ?></strong></span>
        <span class="text-muted">😠 Negativo: <strong><?php echo e($totales->sentimiento_negativo); ?></strong></span>
    </div>
</div>


<div class="card" style="padding: 1rem; overflow: hidden;">
    <table id="vendorSummaryTable" class="display" style="width:100%">
        <thead>
            <tr>
                <th>Vendedor</th>
                <th>Total</th>
                <th>Salientes</th>
                <th>Entrantes</th>
                <th>Perdidas</th>
                <th>Dur. Prom.</th>
                <th>Score Prom.</th>
                <th>Sentimiento</th>
                <th>Última Actividad</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $vendedores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div style="width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #4f46e5); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem;">
                                <?php echo e(strtoupper(substr($v->nombre, 0, 2))); ?>

                            </div>
                            <div>
                                <div style="font-weight: 600;"><?php echo e($v->nombre); ?></div>
                                <div class="text-muted" style="font-size: 0.8rem;"><?php echo e($v->telefono_corporativo ?? ''); ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span style="font-weight: 700; font-size: 1.1rem; color: var(--primary);"><?php echo e($v->total_llamadas); ?></span>
                    </td>
                    <td>
                        <span class="badge badge-info"><?php echo e($v->llamadas_salientes); ?></span>
                    </td>
                    <td>
                        <span class="badge badge-success"><?php echo e($v->llamadas_entrantes); ?></span>
                    </td>
                    <td>
                        <?php if($v->llamadas_perdidas > 0): ?>
                            <span class="badge badge-warning"><?php echo e($v->llamadas_perdidas); ?></span>
                        <?php else: ?>
                            <span class="text-muted">0</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo e(gmdate("i:s", $v->duracion_promedio ?? 0)); ?></td>
                    <td>
                        <?php
                            $score = $v->score_promedio ?? 0;
                            $scoreColor = $score >= 61 ? '#22c55e' : ($score >= 31 ? '#f59e0b' : '#ef4444');
                        ?>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 50px; height: 6px; background: #e5e7eb; border-radius: 3px; overflow: hidden;">
                                <div style="width: <?php echo e(min(100, $score)); ?>%; height: 100%; background: <?php echo e($scoreColor); ?>; border-radius: 3px;"></div>
                            </div>
                            <span style="font-weight: 700; color: <?php echo e($scoreColor); ?>;"><?php echo e($score); ?>%</span>
                        </div>
                    </td>
                    <td>
                        <?php
                            $totalSentV = max(1, $v->sentimiento_positivo + $v->sentimiento_negativo + $v->sentimiento_neutral);
                        ?>
                        <div style="display: flex; gap: 0.25rem; align-items: center;">
                            <?php if($v->sentimiento_positivo > 0): ?>
                                <span title="Positivo" style="font-size: 0.85rem;">😊<?php echo e($v->sentimiento_positivo); ?></span>
                            <?php endif; ?>
                            <?php if($v->sentimiento_neutral > 0): ?>
                                <span title="Neutral" style="font-size: 0.85rem;">😐<?php echo e($v->sentimiento_neutral); ?></span>
                            <?php endif; ?>
                            <?php if($v->sentimiento_negativo > 0): ?>
                                <span title="Negativo" style="font-size: 0.85rem;">😠<?php echo e($v->sentimiento_negativo); ?></span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <?php if($v->ultima_llamada): ?>
                            <span class="text-muted"><?php echo e(\Carbon\Carbon::parse($v->ultima_llamada)->format('d/m/Y H:i')); ?></span>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
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
    $('#vendorSummaryTable').DataTable({
        dom: '<"top"Bfl>rt<"bottom"ip>',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '📥 Exportar Excel',
                title: 'Resumen por Vendedor - <?php echo e(now()->format("d-m-Y")); ?>',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 8]
                }
            }
        ],
        language: {
            search: "Buscar:",
            lengthMenu: "Mostrar _MENU_ registros",
            info: "Mostrando _START_ a _END_ de _TOTAL_ vendedores",
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
        pageLength: 25,
        order: [[1, 'desc']],
        responsive: true
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/reports/vendors.blade.php ENDPATH**/ ?>