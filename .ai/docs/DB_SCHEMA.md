# DB_SCHEMA.md

## Base de datos
MySQL 8+

## Tablas principales

### vendedores
- id
- nombre
- usuario
- password_hash
- telefono_corporativo
- estado
- created_at
- updated_at

### dispositivos
- id
- vendedor_id
- device_uuid
- marca
- modelo
- version_android
- activo
- ultimo_sync_at
- created_at
- updated_at

### clientes
- id
- telefono_normalizado
- nombre_referencial nullable
- created_at
- updated_at

### llamadas
- id
- vendedor_id
- dispositivo_id
- cliente_id
- telefono_origen
- telefono_destino
- telefono_cliente_normalizado
- tipo_llamada
- fecha_inicio
- fecha_fin
- duracion_segundos
- estado_proceso
- transcript_text nullable
- summary_text nullable
- created_at
- updated_at

### audios_llamada
- id
- llamada_id
- storage_disk
- storage_path
- file_name
- mime_type
- file_size
- file_hash
- source_mode
- uploaded_at
- created_at
- updated_at

### analisis_llamada
- id
- llamada_id
- modelo_version
- sentimiento_cliente
- tono_general
- intencion_comercial
- score_venta
- objeciones_json
- siguiente_accion
- analisis_json
- analizado_at
- created_at
- updated_at

### logs_sincronizacion
- id
- dispositivo_id
- llamada_id nullable
- tipo_evento
- payload_json
- resultado
- created_at

## Relaciones
- un vendedor tiene muchos dispositivos
- un vendedor tiene muchas llamadas
- un cliente tiene muchas llamadas
- una llamada tiene un audio principal
- una llamada tiene un análisis principal

## Índices recomendados
- clientes.telefono_normalizado unique
- llamadas.vendedor_id + fecha_inicio
- llamadas.telefono_cliente_normalizado + fecha_inicio
- llamadas.estado_proceso
- audios_llamada.llamada_id unique
- analisis_llamada.llamada_id unique

## Observaciones
- `telefono_cliente_normalizado` se guarda también en `llamadas` para acelerar búsqueda.
- `transcript_text` en `llamadas` facilita consultas rápidas sin join inmediato.
- `analisis_json` conserva salida completa del motor IA.
