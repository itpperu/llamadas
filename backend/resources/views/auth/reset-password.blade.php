<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Call SYNC - Nueva Contraseña</title>
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
            min-height: 100vh;
            overflow: hidden;
        }
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
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
            width: 100%;
            max-width: 420px;
            border: 1px solid var(--border);
            text-align: center;
        }
        .icon { font-size: 2.5rem; margin-bottom: 1rem; }
        h1 { font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem; }
        .subtitle { color: var(--text-muted); font-size: 0.9rem; margin-bottom: 2rem; }
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
        .btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
        .error { color: #ef4444; font-size: 0.85rem; margin-bottom: 1.5rem; text-align: left; background: rgba(239,68,68,0.1); padding: 0.75rem 1rem; border-radius: 8px; }
        .hint { color: var(--text-muted); font-size: 0.78rem; margin-top: 0.4rem; }
        .back-link { display: block; margin-top: 1.5rem; color: var(--text-muted); font-size: 0.85rem; transition: color 0.2s; }
        .back-link:hover { color: var(--primary); }
    </style>
</head>
<body>
    <div class="bg-gradient"></div>
    <div class="card">
        <div class="icon">🔑</div>
        <h1>Nueva Contraseña</h1>
        <p class="subtitle">Elige una contraseña segura para tu cuenta.</p>

        @if($errors->any())
            <div class="error">
                @foreach($errors->all() as $error)• {{ $error }}<br>@endforeach
            </div>
        @endif

        <form action="{{ route('password.update') }}" method="POST" onsubmit="handleSubmit(this)">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="form-group">
                <label>Correo Electrónico</label>
                <input type="email" name="email" value="{{ old('email', $email) }}" required autocomplete="email">
            </div>

            <div class="form-group">
                <label>Nueva Contraseña</label>
                <input type="password" name="password" required autocomplete="new-password" placeholder="••••••••">
                <div class="hint">Mínimo 8 caracteres, mayúsculas, minúsculas y números.</div>
            </div>

            <div class="form-group">
                <label>Confirmar Contraseña</label>
                <input type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••">
            </div>

            <button type="submit" id="submitBtn" class="btn">Guardar nueva contraseña</button>
        </form>

        <a href="{{ route('login') }}" class="back-link">← Volver al inicio de sesión</a>
    </div>

    <script>
        function handleSubmit(form) {
            var btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.textContent = 'Guardando...';
        }
    </script>
</body>
</html>
