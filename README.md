# mi-proyecto

Sistema de monitoreo y análisis de llamadas comerciales desde dispositivos móviles corporativos.

## Estructura principal
- `android-app/`: proyecto Android Studio
- `backend/`: proyecto Laravel
- `services/ai-worker/`: procesamiento Python
- `.ai/`: contexto persistente y documentación operativa

## Objetivo del MVP
Tener un flujo completo:
1. detectar llamada en Android
2. registrar metadata
3. adjuntar o ubicar grabación nativa
4. subir audio al backend
5. transcribir
6. analizar
7. visualizar reporte por vendedor, cliente y fecha

## 📋 Protocolo de Validación del Piloto (MVP)
Sigue este checklist antes de entregar el equipo a un vendedor real:

### 1. Preparación y Registro
- [ ] **Docker arriba:** Ejecutar `docker compose up -d` en la carpeta `backend`.
- [ ] **IA arriba:** Ejecutar el microservicio Python (Puerto 8001).
- [ ] **Worker arriba:** Ejecutar `php artisan queue:work` en Laravel.
- [ ] **Registro de Vendedor:** Ejecutar el comando para el vendedor específico:
  `php artisan app:register-vendedor "Nombre" "usuario" "clave" "uuid-vendedor"`
- [ ] **Credenciales Web:** Asegurarse de tener el acceso `admin@grabacion.com` / `admin123`.

### 2. Ciclo en la App (Vendedor)
- [ ] **Login Exitoso:** El vendedor ingresa sus credenciales y el token es generado.
- [ ] **Detección de Llamada:** Al finalizar una llamada, la app detecta el estado `IDLE` y registra la metadata.
- [ ] **Sincronización:** Ver en el log de Laravel (`storage/logs/laravel.log`) que la llamada entró como `audio_pendiente`.
- [ ] **Subida de Audio:** El vendedor asocia el audio manualmente y presiona "Subir". La app confirma el envío.

### 3. Procesamiento e IA
- [ ] **Disparo de Tarea:** Ver en la terminal del `queue:work` que se inicia `ProcessCallAIJob`.
- [ ] **Transcripción:** Ver en la terminal de Python que Whisper procesa el audio y genera texto.
- [ ] **Persistencia:** Verificar en la tabla `llamadas` que `transcript_text` y `estado_proceso = 'analizada'` estén correctos.

### 4. Visualización (Supervisor)
- [ ] **Panel Web:** Entrar a `http://localhost:8000/reports` y filtrar por el vendedor del piloto.
- [ ] **Análisis de Venta:** Entrar al detalle y validar que el **Score de Venta**, **Sentimiento** y **Objeciones** coincidan con el audio real.

## 🔍 Guía de Diagnóstico de Logs
- **Laravel:** `backend/storage/logs/laravel.log` (Errores de API y DB).
- **Python:** Consola de Uvicorn (Errores de Whisper y FFmpeg).
- **Worker:** Consola de artisan queue (Errores de comunicación con la IA).
- **Android:** Logcat en Android Studio (Errores de red o permisos).

## Documentación
La documentación técnica detallada está en la carpeta `.ai/`.
