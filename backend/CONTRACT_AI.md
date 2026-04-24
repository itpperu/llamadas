# Contrato de Integración: Laravel <-> Python AI Worker

Este documento define la interacción HTTP entre el Backend (Laravel) y el Motor de IA (Python).

## 1. Disparador (Laravel -> Python)

Laravel invoca a Python vía HTTP POST cuando una llamada tiene el audio subido y está lista para transcribirse y analizarse. El llamado es ejecutado asíncronamente por el contenedor `laravel_worker`.

- **Endpoint URL:** Definido en el `.env` de Laravel como `AI_WORKER_URL` (Ej: `http://host.docker.internal:8001/api/analyze`)
- **Método:** `POST`
- **Content-Type:** `application/json`

### Payload de Envío (Ejemplo):
```json
{
    "call_id": 150,
    "audio_path": "/var/www/html/storage/app/audios/nombre_archivo.mp3",
    "metadata": {
        "duracion": 300,
        "tipo": "saliente",
        "telefono_cliente": "+51999999999"
    }
}
```
*(Nota: El `audio_path` es la ruta absoluta dentro del contenedor o el path relativo al storage, ambos servicios deben poder acceder al volumen compartido o Laravel deberá enviar la pre-signed URL en el futuro si usan S3).*

## 2. Respuesta Esperada (Python -> Laravel)

Python procesa el audio y devuelve los resultados. Para el MVP actual (procesamiento síncrono HTTP que bloquea el Job en Laravel de forma controlada hasta obtener respuesta):

- **Status Code Exitoso:** `200 OK`
- **Content-Type:** `application/json`

### Payload de Respuesta:
```json
{
    "transcript_text": "Cliente: Hola, me interesa el servicio... Vendedor: Perfecto...",
    "summary_text": "El cliente muestra interés en corporativo y pide propuesta.",
    "analisis": {
        "modelo_version": "whisper-v1 + gpt-4-turbo",
        "sentimiento_cliente": "positivo",
        "tono_general": "interesado",
        "intencion_comercial": "alta",
        "score_venta": 85,
        "objeciones_json": ["precio_elevado", "tiempo_implementacion"],
        "siguiente_accion": "Agendar reunión de seguimiento mañana."
    }
}
```

## 3. Manejo de Errores y Robustez

Si Python responde con un error `4xx` o `5xx`, o si el request hace timeout (tarda demasiado), Laravel gestiona el estado así:
1. Pone el `estado_proceso` de la llamada en `error`.
2. Falla intencionadamente la tarea actual (`Exception`).
3. La tarea de Job pasa automáticamente al estado en base de datos (`failed_jobs`).
4. Permite reprocesos manuales por parte del administrador disparando el endpoint de `/reprocess`.
