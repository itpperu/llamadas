@extends('layouts.app')

@section('content')
<div class="header">
    <div style="display: flex; align-items: center; gap: 1rem;">
        <a href="{{ route('reports.index') }}" class="btn" style="padding: 0.5rem; background: #fff; border: 1px solid #ddd;">← Volver</a>
        <h1>Detalle de Interacción #{{ $call->id }}</h1>
    </div>
    
    <div style="display:flex; gap: 1rem;">
        <form action="{{ route('reports.reprocess', $call->id) }}" method="POST" class="ajax-form">
            @csrf
            <button type="submit" class="btn" style="background: #f1f5f9; color: #4338ca; border: 1px solid #c7d2fe;" data-loader-text="Enviando a cola de análisis...">
                🔄 Reprocesar IA
            </button>
        </form>
        @if($call->audio)
            <a href="{{ route('reports.audio', $call->id) }}" target="_blank" class="btn" style="background: #f1f5f9; color: #4338ca; border: 1px solid #c7d2fe;">
                ⬇️ Descargar Audio
            </a>
        @endif
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 350px; gap: 1.5rem;">
    <!-- Columna Izquierda: Audio y Transcripción -->
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        
        <!-- Audio Player Card -->
        <div class="card">
            <h3 style="margin-top:0;">Grabación de la llamada</h3>
            @if($call->audio)
                <audio controls style="width: 100%; border-radius: 8px; margin-top: 1rem; border: 1px solid #ddd;">
                    <source src="{{ route('reports.audio', $call->id) }}" type="audio/mpeg">
                    Tu navegador no soporta el audio nativo.
                </audio>
                <div style="display:flex; justify-content: space-between; margin-top: 1rem;" class="text-muted">
                    <span>Tamaño: {{ number_format($call->audio->file_size / 1024, 2) }} KB</span>
                    <span>Modo Captura: {{ $call->audio->source_mode }}</span>
                    <span>Mime: {{ $call->audio->mime_type }}</span>
                </div>
            @else
                <div style="padding: 2rem; background: #f8fafc; border-radius: 8px; text-align: center; border: 2px dashed #cbd5e1; color: var(--text-muted);">
                    🔇 Audio no disponible para esta interacción.
                </div>
            @endif
        </div>

        <!-- Transcription Card -->
        <div class="card" style="min-height: 400px;">
            <div style="display:flex; justify-content: space-between; align-items:center; border-bottom: 2px solid #f1f5f9; padding-bottom: 0.5rem; margin-bottom: 1rem;">
                <h3 style="margin:0;">Transcripción completa</h3>
                @if($call->analisis)
                    <span class="badge badge-success">Procesado con {{ $call->analisis->modelo_version }}</span>
                @endif
            </div>
            
            <div style="line-height: 1.7; font-size: 1rem; white-space: pre-wrap; color: #334155; padding: 1rem; background: #fffdf9; border-radius: 8px; border-left: 4px solid #fde68a;">
                @if($call->transcript_text)
                    {!! nl2br(e($call->transcript_text)) !!}
                @else
                    <div class="text-muted" style="text-align: center; margin-top: 4rem;">
                        Transcripción aún no generada o pendiente de procesamiento.
                    </div>
                @endif
            </div>

            @if($call->summary_text)
                <div style="margin-top: 1.5rem; padding: 1.25rem; background: #f0f9ff; border-radius: 8px; border: 1px solid #bae6fd;">
                    <strong style="color: #0369a1; display: block; margin-bottom: 0.5rem;">✨ Resumen de la IA</strong>
                    <p style="margin:0; color: #0c4a6e; font-style: italic;">"{{ $call->summary_text }}"</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Columna Derecha: Metadata y Análisis de venta -->
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        
        <!-- Metadata Info -->
        <div class="card">
            <h3 style="margin-top:0; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.5rem;">Información</h3>
            <div style="display: flex; flex-direction: column; gap: 1rem; margin-top: 1rem;">
                <div>
                    <span class="text-muted">Estado Actual:</span><br>
                    <span class="badge {{ $call->estado_proceso == 'analizada' ? 'badge-success' : 'badge-info' }}" style="margin-top: 0.25rem;">
                        {{ strtoupper(str_replace('_', ' ', $call->estado_proceso)) }}
                    </span>
                </div>
                <div>
                    <span class="text-muted">Vendedor Responsable:</span><br>
                    <span style="font-weight: 600;">{{ $call->vendedor->nombre }}</span><br>
                    <span class="text-muted">ID: {{ $call->vendedor_id }}</span>
                </div>
                <div>
                    <span class="text-muted">Cliente / Prospecto:</span><br>
                    <span style="font-weight: 600;">{{ $call->cliente->nombre_referencial }}</span><br>
                    <span style="color: #6366f1;">{{ $call->telefono_cliente_normalizado }}</span>
                </div>
                <div>
                    <span class="text-muted">Duración Total:</span><br>
                    <span style="font-size: 1.25rem; font-weight: 700;">{{ gmdate("H:i:s", $call->duracion_segundos) }}</span>
                </div>
                <div>
                    <span class="text-muted">Inicio:</span><br>
                    <span>{{ $call->fecha_inicio->format('d M Y, H:i:s') }}</span>
                </div>
                <div>
                    <span class="text-muted">Fin:</span><br>
                    <span>{{ $call->fecha_fin ? $call->fecha_fin->format('d M Y, H:i:s') : '-' }}</span>
                </div>
            </div>
        </div>

        <!-- AI Analysis Result Card -->
        @if($call->analisis)
            <div class="card" style="background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%); border-top: 5px solid #6366f1;">
                <h3 style="margin-top:0; color: #4338ca;">Análisis de Venta</h3>
                
                <!-- Score Circle -->
                <div style="display: flex; align-items: center; justify-content: center; margin: 1.5rem 0; flex-direction: column;">
                    <div style="width: 100px; height: 100px; border-radius: 50%; border: 8px solid #e2e8f0; border-top-color: #6366f1; border-right-color: #6366f1; display: flex; align-items: center; justify-content: center; transform: rotate(45deg);">
                        <div style="transform: rotate(-45deg); font-size: 1.75rem; font-weight: 800; color: #4338ca;">
                            {{ $call->analisis->score_venta }}%
                        </div>
                    </div>
                    <span class="text-muted" style="margin-top: 0.5rem; font-weight: 700; text-transform: uppercase;">Probabilidad de Venta</span>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; border-top: 1px solid #f1f5f9; padding-top: 1rem; margin-top: 1rem;">
                    <div>
                        <span class="text-muted">Sentimiento:</span><br>
                        <span style="font-weight: 700; color: {{ 
                            $call->analisis->sentimiento_cliente == 'positivo' ? '#16a34a' : 
                            ($call->analisis->sentimiento_cliente == 'negativo' ? '#dc2626' : '#475569') 
                        }}">
                            {{ strtoupper($call->analisis->sentimiento_cliente) }}
                        </span>
                    </div>
                    <div>
                        <span class="text-muted">Intención:</span><br>
                        <span style="font-weight: 700;">{{ strtoupper($call->analisis->intencion_comercial) }}</span>
                    </div>
                </div>

                <!-- Next Action -->
                @if($call->analisis->siguiente_accion)
                    <div style="margin-top: 1.5rem; padding: 1rem; background: #e0f2fe; border-radius: 8px; border: 1px solid #0284c7;">
                        <strong style="color: #0369a1;">Siguiente Acción Sugerida:</strong><br>
                        <p style="margin-top: 0.25rem; font-size: 0.9rem; color: #075985;">
                            {{ $call->analisis->siguiente_accion }}
                        </p>
                    </div>
                @endif

                <!-- Objections -->
                <div style="margin-top: 1.5rem;">
                    <strong class="text-muted">Objeciones encontradas:</strong><br>
                    <div style="display:flex; flex-wrap: wrap; gap: 0.5rem; margin-top:0.5rem;">
                        @forelse($call->analisis->objeciones_json ?? [] as $obj)
                            <span class="badge" style="background: #fee2e2; color: #991b1b;">{{ $obj }}</span>
                        @empty
                            <span class="text-muted font-italic">Ninguna detectada</span>
                        @endforelse
                    </div>
                </div>

                <div class="text-muted" style="margin-top: 2rem; font-size: 0.7rem; text-align: center;">
                    Analizado el {{ $call->analisis->analizado_at ? $call->analisis->analizado_at->format('d/m/Y H:i') : '-' }}
                </div>
            </div>
        @else
            <div class="card" style="text-align: center; border: 2px dashed #cbd5e1; background: #f8fafc;">
                <p class="text-muted">Esperando resultados de análisis IA...</p>
            </div>
        @endif

    </div>
</div>
@endsection
