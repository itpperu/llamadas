@extends('layouts.app')

@section('content')
<div class="header">
    <div style="display: flex; align-items: center; gap: 1rem;">
        <a href="{{ route('vendedores.index') }}" class="btn" style="padding: 0.5rem; background: #fff; border: 1px solid #ddd;">← Volver</a>
        <h1>Nuevo Vendedor Pilot</h1>
    </div>
</div>

<div class="card" style="max-width: 600px;">
    @if ($errors->any())
        <div style="padding: 1rem; background: #fee2e2; color: #991b1b; border-radius: 8px; margin-bottom: 2rem;">
            @foreach ($errors->all() as $error)
                • {{ $error }}<br>
            @endforeach
        </div>
    @endif

    <form action="{{ route('vendedores.store') }}" method="POST" class="ajax-form">
        @csrf
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div style="grid-column: span 2;">
                <label class="text-muted">Nombre Completo del Vendedor</label><br>
                <input type="text" name="nombre" value="{{ old('nombre') }}" placeholder="Ej: Juan Pérez" style="width:100%; height:40px; border-radius:8px; border:1px solid #ddd; padding: 0.5rem; margin-top: 0.5rem;" required>
            </div>
            <div>
                <label class="text-muted">Usuario Acceso App</label><br>
                <input type="text" name="usuario" value="{{ old('usuario') }}" placeholder="usuario123" style="width:100%; height:40px; border-radius:8px; border:1px solid #ddd; padding: 0.5rem; margin-top: 0.5rem;" required>
            </div>
            <div>
                <label class="text-muted">Contraseña Inicial</label><br>
                <input type="password" name="password" style="width:100%; height:40px; border-radius:8px; border:1px solid #ddd; padding: 0.5rem; margin-top: 0.5rem;" required>
            </div>
            <div>
                <label class="text-muted">📞 Teléfono Corporativo</label><br>
                <input type="text" name="telefono_corporativo" value="{{ old('telefono_corporativo') }}" placeholder="Ej: +51999888777" style="width:100%; height:40px; border-radius:8px; border:1px solid #ddd; padding: 0.5rem; margin-top: 0.5rem;">
            </div>
            <div style="grid-column: span 2;">
                <label class="text-muted">📱 UUID del Dispositivo Corporativo (Android ID) <span style="color:#94a3b8; font-weight:400;">— Opcional</span></label><br>
                <input type="text" name="device_uuid" value="{{ old('device_uuid') }}" placeholder="Ej: 8e8f8f8f8f8f8f8f" style="width:100%; height:40px; border-radius:8px; border:1px solid #ddd; padding: 0.5rem; margin-top: 0.5rem;">
                <small class="text-muted">
                    Puedes dejarlo en blanco ahora y asignarlo después desde <strong>Editar vendedor</strong> una vez que tengas el celular en mano.<br>
                    El UUID se obtiene en la app Android: pestaña <strong>⚙️ Configuración → Dispositivo</strong>.
                </small>
            </div>
        </div>

        <div style="margin-top: 2rem; border-top: 1px solid #f1f5f9; padding-top: 1.5rem; display: flex; justify-content: flex-end;">
            <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem;" data-loader-text="Registrando vendedor...">🚀 Dar de Alta Vendedor</button>
        </div>
    </form>
</div>
@endsection
