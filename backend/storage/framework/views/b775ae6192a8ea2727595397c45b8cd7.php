<?php $__env->startSection('content'); ?>
<div class="header">
    <div style="display: flex; align-items: center; gap: 1rem;">
        <a href="<?php echo e(route('vendedores.index')); ?>" class="btn" style="padding: 0.5rem; background: #fff; border: 1px solid #ddd;">← Volver</a>
        <h1>Nuevo Vendedor Pilot</h1>
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

    <form action="<?php echo e(route('vendedores.store')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div style="grid-column: span 2;">
                <label class="text-muted">Nombre Completo del Vendedor</label><br>
                <input type="text" name="nombre" value="<?php echo e(old('nombre')); ?>" placeholder="Ej: Juan Pérez" style="width:100%; height:40px; border-radius:8px; border:1px solid #ddd; padding: 0.5rem; margin-top: 0.5rem;" required>
            </div>
            <div>
                <label class="text-muted">Usuario Acceso App</label><br>
                <input type="text" name="usuario" value="<?php echo e(old('usuario')); ?>" placeholder="usuario123" style="width:100%; height:40px; border-radius:8px; border:1px solid #ddd; padding: 0.5rem; margin-top: 0.5rem;" required>
            </div>
            <div>
                <label class="text-muted">Contraseña Inicial</label><br>
                <input type="password" name="password" style="width:100%; height:40px; border-radius:8px; border:1px solid #ddd; padding: 0.5rem; margin-top: 0.5rem;" required>
            </div>
            <div style="grid-column: span 2;">
                <label class="text-muted">📱 UUID del Dispositivo Corporativo (Android ID)</label><br>
                <input type="text" name="device_uuid" value="<?php echo e(old('device_uuid')); ?>" placeholder="Ej: 8e8f8f8f8f8f8f8f" style="width:100%; height:40px; border-radius:8px; border:1px solid #ddd; padding: 0.5rem; margin-top: 0.5rem;" required>
                <small class="text-muted">Este ID es necesario para autorizar el login desde el celular específico del vendedor.</small>
            </div>
        </div>

        <div style="margin-top: 2rem; border-top: 1px solid #f1f5f9; padding-top: 1.5rem; display: flex; justify-content: flex-end;">
            <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem;">🚀 Dar de Alta Vendedor</button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/vendedores/create.blade.php ENDPATH**/ ?>