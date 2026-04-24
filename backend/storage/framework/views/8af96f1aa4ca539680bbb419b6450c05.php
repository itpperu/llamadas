<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>AI Call Monitoring - Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --secondary: #101827;
            --accent: #14b8a6;
            --danger: #ef4444;
            --warning: #f59e0b;
            --success: #22c55e;
            --bg-light: #f9fafb;
            --card-bg: #ffffff;
            --text-main: #111827;
            --text-muted: #6b7280;
            --border: #e5e7eb;
            --radius: 12px;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-main);
            margin: 0;
            padding: 0;
            display: flex;
            min-height: 100vh;
        }

        a { text-decoration: none; color: inherit; }

        /* Sidebar */
        aside {
            width: 260px;
            box-sizing: border-box;
            background-color: var(--secondary);
            color: white;
            padding: 2rem 1.5rem;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            overflow: hidden;
        }

        .logo { font-size: 1.25rem; font-weight: 700; color: var(--accent); }

        .nav-links {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            flex: 1;
            margin-top: 1.5rem;
        }
        .nav-item {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            transition: background 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .nav-item:hover, .nav-item.active { background-color: rgba(255, 255, 255, 0.1); color: var(--accent); }

        .logout-section {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255,255,255,0.08);
            flex-shrink: 0;
        }

        /* Main Content */
        main {
            margin-left: 260px;
            flex: 1;
            padding: 2.5rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .header h1 { font-size: 1.85rem; font-weight: 700; margin: 0; }

        /* Generic Card */
        .card {
            background-color: var(--card-bg);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            margin-bottom: 1.5rem;
        }

        /* Status Badge */
        .badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.6rem;
            border-radius: 99px;
            text-transform: capitalize;
        }
        .badge-success { background-color: #dcfce7; color: #166534; }
        .badge-warning { background-color: #fef9c3; color: #854d0e; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; }
        .badge-info { background-color: #dbeafe; color: #1e40af; }

        /* Buttons */
        .btn {
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }
        .btn-primary { background-color: var(--primary); color: white; }
        .btn-primary:hover { background-color: var(--primary-hover); transform: translateY(-1px); }
        .btn-success { background-color: var(--success); color: white; }
        .btn-success:hover { background-color: #16a34a; transform: translateY(-1px); }
        .btn:disabled, .btn.is-loading {
            opacity: 0.6;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* Table base (overridden by DataTables where used) */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        th { text-align: left; font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase; padding: 1rem; border-bottom: 2px solid var(--border); }
        td { padding: 1rem; border-bottom: 1px solid var(--border); font-size: 0.95rem; }
        tr:hover { background-color: #f8fafc; }

        .pagination { margin-top: 1.5rem; display: flex; gap: 0.5rem; justify-content: center; }

        /* Utility */
        .text-muted { color: var(--text-muted); font-size: 0.85rem; }
        .mt-4 { margin-top: 1rem; }

        /* DataTables custom styling to match our design */
        .dataTables_wrapper {
            padding: 0.5rem 0;
        }
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 0.5rem 0.75rem;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            outline: none;
            transition: border-color 0.2s;
        }
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        .dataTables_wrapper .dataTables_length select {
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 0.4rem 0.5rem;
            font-family: 'Inter', sans-serif;
        }
        table.dataTable thead th {
            border-bottom: 2px solid var(--border) !important;
            font-size: 0.8rem;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 600;
        }
        table.dataTable tbody td {
            border-bottom: 1px solid #f1f5f9 !important;
            font-size: 0.92rem;
        }
        table.dataTable tbody tr:hover {
            background-color: #f8fafc !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--primary) !important;
            color: white !important;
            border: none !important;
            border-radius: 6px;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #e0e7ff !important;
            color: var(--primary) !important;
            border: none !important;
            border-radius: 6px;
        }
        .dataTables_wrapper .dataTables_info {
            font-size: 0.85rem;
            color: var(--text-muted);
        }
        /* DataTables Buttons */
        .dt-buttons .dt-button {
            background: var(--success) !important;
            color: white !important;
            border: none !important;
            border-radius: 8px !important;
            padding: 0.5rem 1rem !important;
            font-family: 'Inter', sans-serif !important;
            font-weight: 600 !important;
            font-size: 0.85rem !important;
            transition: all 0.2s !important;
        }
        .dt-buttons .dt-button:hover {
            background: #16a34a !important;
            transform: translateY(-1px);
        }

        /* Overlay Loader for AJAX */
        .ajax-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.35);
            z-index: 9999;
            backdrop-filter: blur(2px);
            justify-content: center;
            align-items: center;
        }
        .ajax-overlay.active {
            display: flex;
        }
        .ajax-loader-box {
            background: white;
            padding: 2.5rem 3rem;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }
        .spinner {
            width: 42px;
            height: 42px;
            border: 4px solid #e5e7eb;
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .ajax-loader-box span {
            font-weight: 600;
            color: var(--text-main);
            font-size: 0.95rem;
        }

        /* Logout button */
        .logout-btn {
            width: 100%;
            background: transparent;
            border: 1px solid rgba(255,255,255,0.12);
            color: #64748b;
            padding: 0.6rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.875rem;
            font-family: inherit;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: background 0.2s, color 0.2s, border-color 0.2s;
        }
        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.18);
            color: #fca5a5;
            border-color: rgba(239, 68, 68, 0.4);
        }

        /* Page header */
        .page-header-title {
            font-size: 1.65rem;
            font-weight: 700;
            margin: 0;
            color: var(--text-main);
            letter-spacing: -0.02em;
        }
        .page-header-sub {
            font-size: 0.82rem;
            color: var(--text-muted);
            margin-top: 0.2rem;
        }

        @media (max-width: 768px) {
            aside { display: none; }
            main { margin-left: 0; padding: 1.5rem; }
        }
    </style>
    <?php echo $__env->yieldContent('extra_css'); ?>
</head>
<body>

    <!-- Global AJAX Loader Overlay -->
    <div class="ajax-overlay" id="ajaxOverlay">
        <div class="ajax-loader-box">
            <div class="spinner"></div>
            <span id="ajaxLoaderText">Procesando...</span>
        </div>
    </div>

    <aside>
        <div class="logo">AI CALL SYNC</div>
        <nav class="nav-links">
            <a href="<?php echo e(route('reports.index')); ?>" class="nav-item <?php echo e(request()->routeIs('reports.index') || request()->routeIs('reports.show') ? 'active' : ''); ?>">
                📊 Reportes
            </a>
            <a href="<?php echo e(route('reports.vendors')); ?>" class="nav-item <?php echo e(request()->routeIs('reports.vendors') ? 'active' : ''); ?>">
                📈 Resumen Vendedores
            </a>
            <a href="<?php echo e(route('vendedores.index')); ?>" class="nav-item <?php echo e(request()->routeIs('vendedores.*') ? 'active' : ''); ?>">
                👥 Vendedores
            </a>
            <a href="<?php echo e(route('settings.index')); ?>" class="nav-item <?php echo e(request()->routeIs('settings.*') ? 'active' : ''); ?>">
                🎯 Configuración
            </a>
        </nav>

        <div class="logout-section">
            <div style="font-size: 0.78rem; color: #475569; margin-bottom: 0.6rem; padding: 0 0.25rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                <?php echo e(auth()->user()->name ?? ''); ?>

            </div>
            <form action="<?php echo e(route('logout')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <button type="submit" class="logout-btn">
                    🚪 Cerrar sesión
                </button>
            </form>
        </div>
    </aside>

    <main>
        <?php if(session('success')): ?>
            <div class="card" style="border-left: 4px solid var(--success); color: #166534; background: #f0fdf4;">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <?php if($errors->any()): ?>
            <div class="card" style="border-left: 4px solid var(--danger); color: #991b1b; background: #fef2f2;">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div><?php echo e($error); ?></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <!-- jQuery + DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

    <script>
        // ============================================================
        // Global AJAX Loader System
        // Usage: Add class "btn-ajax" to any button/link for auto-loader.
        // Or call showLoader('text') / hideLoader() manually.
        // ============================================================
        function showLoader(text) {
            document.getElementById('ajaxLoaderText').textContent = text || 'Procesando...';
            document.getElementById('ajaxOverlay').classList.add('active');
        }
        function hideLoader() {
            document.getElementById('ajaxOverlay').classList.remove('active');
        }

        $(document).ready(function() {
            // Auto-attach loader to forms with class .ajax-form
            $(document).on('submit', 'form.ajax-form', function() {
                var $btn = $(this).find('button[type="submit"]');
                var loaderText = $btn.data('loader-text') || 'Procesando...';
                $btn.prop('disabled', true).addClass('is-loading');
                showLoader(loaderText);
            });

            // Auto-attach loader to buttons/links with class .btn-ajax
            $(document).on('click', '.btn-ajax', function() {
                var $el = $(this);
                var loaderText = $el.data('loader-text') || 'Cargando...';
                $el.prop('disabled', true).addClass('is-loading');
                showLoader(loaderText);
            });

            // CSRF token for all AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        });
    </script>

    <?php echo $__env->yieldContent('extra_js'); ?>

</body>
</html>
<?php /**PATH /var/www/html/resources/views/layouts/app.blade.php ENDPATH**/ ?>