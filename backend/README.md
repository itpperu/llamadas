# Backend API - Monitor de Llamadas (MVP)

Backend desarrollado en **Laravel 11** para orquestar metadatos, audios, análisis de IA y reportes.

## Requisitos
- **Docker Desktop** (o Docker Engine) en entorno Windows.
- Sin instalación de PHP local necesaria.

## Pasos para Levantar

1. Entra a la carpeta `backend`:
   ```bash
   cd c:\xampp\htdocs\grabacion_llamada\backend
   ```
2. Levanta los servicios:
   ```bash
   docker-compose up -d --build
   ```

## Documentación para Prueba Manual Extremo a Extremo

A continuación los comandos exactos para probar todo el flujo usando cURL o Postman.  
*(Requiere haber ejecutado el seeder inicial: `docker exec -it laravel_app php artisan db:seed --force`)*

### 1. Login
```bash
curl -X POST http://localhost:8000/api/auth/login \
-H "Accept: application/json" \
-H "Content-Type: application/json" \
-d '{
    "usuario": "vendedor01",
    "password": "password123",
    "device_uuid": "android-001"
}'
```
> **IMPORTANTE:** Guarda el token retornado (ej: `1|h8x...`). Se usará en los siguientes requests.

### 2. Registrar Llamada
*(Asegúrate de cambiar `TU_TOKEN_AQUI` por el token obtenido)*
```bash
curl -X POST http://localhost:8000/api/calls \
-H "Accept: application/json" \
-H "Content-Type: application/json" \
-H "Authorization: Bearer TU_TOKEN_AQUI" \
-d '{
    "device_uuid": "android-001",
    "vendedor_id": 1,
    "telefono_cliente": "+51999999999",
    "tipo": "saliente",
    "fecha_inicio": "2026-03-30 09:00:00",
    "fecha_fin": "2026-03-30 09:05:00",
    "duracion_segundos": 300,
    "estado_audio": "pendiente"
}'
```
> Guardar el `call_id` retornado (ej. `1`).

### 3. Subir Audio
Sube el audio asociado a la llamada en formato Multipart/Form-Data. Para probar localmente crea un archivo vacío `test.mp3`.
```bash
curl -X POST http://localhost:8000/api/calls/1/audio \
-H "Accept: application/json" \
-H "Authorization: Bearer TU_TOKEN_AQUI" \
-F "audio_file=@test.mp3" \
-F "mime_type=audio/mpeg" \
-F "source_mode=auto"
```
> Al terminar, el servidor encolará de inmediato el `ProcessCallAIJob`. El contenedor `laravel_worker` lo atrapará en menos de 3 segundos y procederá a ejecutar el análisis enviando la petición asíncrona simulada.

### 4. Solicitar Reproceso
```bash
curl -X POST http://localhost:8000/api/calls/1/reprocess \
-H "Accept: application/json" \
-H "Authorization: Bearer TU_TOKEN_AQUI"
```

## Arquitectura Finalizada
El MVP cuenta con una arquitectura base limpia e interconectada en su totalidad:
- Endpoints protegidos por Laravel Sanctum en `app/Http/Controllers/`.
- Reglas de validación y control encapsuladas mediante `FormRequests`.
- Integración de Cola Asíncrona soportada mediante la tabla `jobs` en MySQL y consumida por un contenedor lateral `worker` idéntico.
- Un servicio stub `app/Services/AIWorkerService.php` y una firma expuesta `CONTRACT_AI.md` lista para delegarle a Python la lógica externa del procesamiento.

