<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Call SYNC - Iniciar Sesión</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --bg: #0f172a;
            --card-bg: #1e293b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border: #334155;
            --radius: 12px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            overflow: hidden;
        }

        /* Abstract Background */
        .bg-gradient {
            position: fixed;
            top: -20%; left: -10%; 
            width: 50%; height: 50%; 
            background: radial-gradient(circle, rgba(99,102,241,0.15) 0%, rgba(15,23,42,0) 70%);
            z-index: -1;
        }

        .card {
            background-color: var(--card-bg);
            padding: 3rem;
            border-radius: var(--radius);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 400px;
            border: 1px solid var(--border);
            text-align: center;
        }

        h1 { font-size: 1.75rem; font-weight: 700; margin-bottom: 0.5rem; letter-spacing: -0.025em; }
        p { color: var(--text-muted); margin-bottom: 2rem; }

        .form-group { text-align: left; margin-bottom: 1.5rem; }
        label { display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.5rem; }
        
        input {
            width: 100%;
            padding: 0.75rem 1rem;
            background: #0f172a;
            border: 1px solid var(--border);
            border-radius: 8px;
            color: white;
            font-size: 1rem;
            box-sizing: border-box;
            transition: border-color 0.2s;
        }
        input:focus { outline: none; border-color: var(--primary); }

        .btn {
            width: 100%;
            padding: 0.75rem;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn:hover { background-color: var(--primary-hover); transform: translateY(-1px); }

        .error { color: #ef4444; font-size: 0.85rem; margin-bottom: 1.5rem; text-align: left; }
    </style>
</head>
<body>

    <div class="bg-gradient"></div>

    <div class="card">
        <h1>Bienvenido</h1>
        <p>Panel de Monitoreo de Llamadas</p>

        <?php if($errors->any()): ?>
            <div class="error">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    • <?php echo e($error); ?><br>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo e(route('login.post')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <div class="form-group">
                <label>Correo Electrónico</label>
                <input type="email" name="email" required autofocus placeholder="admin@ejemplo.com">
            </div>

            <div class="form-group">
                <label>Contraseña</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>

            <div style="text-align: left; margin-bottom: 1.5rem;">
                <label style="display:inline-flex; align-items:center; cursor:pointer;">
                    <input type="checkbox" name="remember" style="width:auto; margin-right: 0.5rem;">
                    <span>Recordar sesión</span>
                </label>
            </div>

            <button type="submit" class="btn">Acceder al Panel</button>
        </form>

        <a href="<?php echo e(route('password.request')); ?>" style="display:block; margin-top:1.5rem; color:#94a3b8; font-size:0.85rem; text-decoration:none; transition:color 0.2s;"
           onmouseover="this.style.color='#6366f1'" onmouseout="this.style.color='#94a3b8'">
            ¿Olvidaste tu contraseña?
        </a>
    </div>

</body>
</html>
<?php /**PATH /var/www/html/resources/views/auth/login.blade.php ENDPATH**/ ?>