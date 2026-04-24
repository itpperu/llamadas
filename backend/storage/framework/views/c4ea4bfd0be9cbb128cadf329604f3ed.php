<?php $__env->startSection('content'); ?>
<div class="header">
    <div style="display: flex; align-items: center; gap: 1rem;">
        <a href="<?php echo e(route('vendedores.index')); ?>" class="btn" style="padding: 0.5rem; background: #fff; border: 1px solid #ddd;">← Volver</a>
        <h1>Editar Vendedor: <?php echo e($vendedor->nombre); ?></h1>
    </div>
</div>

<div class="card" style="max-width: 600px;">
    <?php if($errors->any()): ?>
        <div style="padding: 1rem; background: #fee2e2; color: #991b1b; border-radius: 8px; margin-bottom: 2rem;">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                • <?php echo e($error); ?><br>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    <form action="<?php echo e(route('vendedores.update', $vendedor->id)); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div style="grid-column: span 2;">
                <label class="text-muted">Nombre Completo del Vendedor</label><br>
                <input type="text" name="nombre" value="<?php echo e(old('nombre', $vendedor->nombre)); ?>" placeholder="Ej: Juan Pérez" style="width:100%; height:40px; border-radius:8px; border:1px solid #ddd; padding: 0.5rem; margin-top: 0.5rem;" required>
            </div>
            <div>
                <label class="text-muted">Usuario Acceso App (No editable)</label><br>
                <input type="text" value="<?php echo e($vendedor->usuario); ?>" style="width:100%; height:40px; border-radius:8px; border:1px solid #ddd; padding: 0.5rem; margin-top: 0.5rem; background: #f1f5f9;" disabled>
            </div>
            <div>
                <label class="text-muted">Estado del Vendedor</label><br>
                <select name="estado" style="width:100%; height:40px; border-radius:8px; border:1px solid #ddd; padding: 0.5rem; margin-top: 0.5rem;">
                    <option value="activo" <?php echo e($vendedor->estado == 'activo' ? 'selected' : ''); ?>>Activo</option>
                    <option value="inactivo" <?php echo e($vendedor->estado == 'inactivo' ? 'selected' : ''); ?>>Inactivo</option>
                </select>
            </div>
            <div>
                <label class="text-muted">📞 Teléfono Corporativo</label><br>
                <input type="text" name="telefono_corporativo" value="<?php echo e(old('telefono_corporativo', $vendedor->telefono_corporativo)); ?>" placeholder="Ej: +51999888777" style="width:100%; height:40px; border-radius:8px; border:1px solid #ddd; padding: 0.5rem; margin-top: 0.5rem;">
            </div>
            <div>
                <label class="text-muted">🔒 Nueva Contraseña (Opcional)</label><br>
                <input type="password" name="password" placeholder="Dejar en blanco para no cambiar" style="width:100%; height:40px; border-radius:8px; border:1px solid #ddd; padding: 0.5rem; margin-top: 0.5rem;">
            </div>
        </div>

        <div style="margin-top: 2rem; border-top: 1px solid #f1f5f9; padding-top: 1.5rem; display: flex; justify-content: flex-end;">
            <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem;">💾 Guardar Cambios</button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/vendedores/edit.blade.php ENDPATH**/ ?>