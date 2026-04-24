@extends('layouts.app')

@section('content')
<div class="header">
    <h1>Gestión de Vendedores</h1>
    <a href="{{ route('vendedores.create') }}" class="btn btn-primary">+ Nuevo Vendedor</a>
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
            @foreach($vendedores as $v)
                <tr>
                    <td style="font-weight: 600;">{{ $v->nombre }}</td>
                    <td>{{ $v->usuario }}</td>
                    <td>{{ $v->telefono_corporativo ?? '-' }}</td>
                    <td>
                        @foreach($v->dispositivos as $d)
                            <div style="font-size: 0.85rem; color: #6366f1;">ID: {{ $d->device_uuid }}</div>
                            <small class="text-muted">{{ $d->marca }} {{ $d->modelo }}</small>
                        @endforeach
                        @if($v->dispositivos->isEmpty())
                            <span class="text-muted">Sin dispositivo</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $v->estado == 'activo' ? 'badge-success' : 'badge-danger' }}">
                            {{ strtoupper($v->estado) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('vendedores.edit', $v->id) }}" class="btn" style="background: #f1f5f9; padding: 0.5rem 0.75rem;">Editar</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@section('extra_js')
<script>
$(document).ready(function() {
    $('#vendedoresTable').DataTable({
        dom: '<"top"Bfl>rt<"bottom"ip>',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '📥 Exportar Excel',
                title: 'Vendedores - {{ now()->format("d-m-Y") }}',
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
@endsection
