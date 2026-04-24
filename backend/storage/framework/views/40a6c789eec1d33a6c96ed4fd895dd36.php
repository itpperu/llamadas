<?php $__env->startSection('content'); ?>
<div class="header">
    <h1>Gestión de Vendedores</h1>
    <a href="<?php echo e(route('vendedores.create')); ?>" class="btn btn-primary">+ Nuevo Vendedor</a>
</div>

<div class="card" style="padding: 1rem; overflow: hidden;">
    <table id="vendedoresTable" class="display" style="width:100%">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Usuario</th>
                <th>Teléfono</th>
                <th>Dispositivo Vinc.</th>
                <th>Estado Vendedor</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $vendedores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td style="font-weight: 600;"><?php echo e($v->nombre); ?></td>
                    <td><?php echo e($v->usuario); ?></td>
                    <td><?php echo e($v->telefono_corporativo ?? '-'); ?></td>
                    <td>
                        <?php $__currentLoopData = $v->dispositivos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div style="font-size: 0.85rem; color: #6366f1;">ID: <?php echo e($d->device_uuid); ?></div>
                            <small class="text-muted"><?php echo e($d->marca); ?> <?php echo e($d->modelo); ?></small>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php if($v->dispositivos->isEmpty()): ?>
                            <span class="text-muted">Sin dispositivo</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge <?php echo e($v->estado == 'activo' ? 'badge-success' : 'badge-danger'); ?>">
                            <?php echo e(strtoupper($v->estado)); ?>

                        </span>
                    </td>
                    <td>
                        <a href="<?php echo e(route('vendedores.edit', $v->id)); ?>" class="btn" style="background: #f1f5f9; padding: 0.5rem 0.75rem;">Editar</a>
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
    $('#vendedoresTable').DataTable({
        dom: '<"top"Bfl>rt<"bottom"ip>',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '📥 Exportar Excel',
                title: 'Vendedores - <?php echo e(now()->format("d-m-Y")); ?>',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4]
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
        pageLength: 25,
        responsive: true,
        columnDefs: [
            { orderable: false, targets: 5 }
        ]
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/vendedores/index.blade.php ENDPATH**/ ?>