# SESSION_HANDOFF.md

## 1. Estado Exacto del Backend (Completado)
El backend Laravel ha sido consolidado como una base robusta, probada y empaquetada. Actualmente cuenta con:
- Entorno multi-contenedor en Docker Compose (`app`, `db`, `nginx`, `worker`).
- Bases de datos migradas según `DB_SCHEMA.md` con índices y constraints (9 migraciones).
- Autenticación real mediante Laravel Sanctum (Bearer Tokens).
- Cola de trabajos asíncronos en base de datos procesando jobs de IA (`ProcessCallAIJob`).
- Controladores enlazados limpiamente vía validaciones de Form Requests (`LoginRequest`, `RegisterCallRequest`, `UploadAudioRequest`).
- Extrema trazabilidad guardando historial constante en la tabla `logs_sincronizacion`.
- Cobertura mínima de pruebas automatizadas (PHPUnit/Pest en `ApiFlowTest.php`).
- Panel web administrativo con login, reportes, detalle de llamada, gestión de vendedores y diagnóstico del sistema.

## 2. Estado Exacto de la App Android (Completada - Pendiente Validación en Dispositivo)
La app Android está implementada en Kotlin bajo el paquete `com.grabacionllamada.app` con la siguiente estructura:

### Capa de datos
- `data/api/ApiService.kt` — Interfaz Retrofit con endpoints de la API Laravel
- `data/api/RetrofitClient.kt` — Configuración de cliente HTTP con Bearer Token
- `data/local/AppDatabase.kt` — Base de datos Room para cola local
- `data/local/CallDao.kt` — DAO para operaciones CRUD sobre llamadas locales
- `data/local/CallEntity.kt` — Entidad Room para persistencia de llamadas
- `data/repository/AuthRepository.kt` — Repositorio de autenticación

### Capa de captura y sincronización
- `receivers/PhoneStateReceiver.kt` — BroadcastReceiver que detecta fin de llamada (IDLE)
- `workers/SyncCallWorker.kt` — Worker que envía metadata a `POST /api/calls`
- `workers/SyncAudioWorker.kt` — Worker que envía audio multipart a `POST /api/calls/{id}/audio`

### Capa de UI
- `ui/login/` — Pantalla de login del vendedor
- `ui/main/` — Pantalla principal con botón de asociación asistida de audio

### Utilidades
- `utils/CallLogReader.kt` — Lectura del call log de Android
- `utils/PhoneNumberNormalizer.kt` — Normalización de números telefónicos
- `utils/SessionManager.kt` — Gestión del token Sanctum en SharedPreferences

### Endurecimiento aplicado
- Validaciones de identidad cruzada (token ↔ vendedor ↔ dispositivo)
- Idempotencia estricta (clave compuesta: dispositivo + fecha + número)
- Limpieza de temporales tras copia multipart
- Soporte para llamadas con duración 0 (perdidas/rechazadas)

## 3. Estado Exacto del Microservicio Python-AI (Completado)
El servicio de IA se encuentra en `services/python-ai/` (NO en `services/ai-worker/`, que es scaffolding obsoleto).

### Estructura implementada
- `app/main.py` — FastAPI con endpoint `POST /process-call` en puerto 8001
- `app/schemas.py` — Contratos Pydantic: `ProcessCallRequest`, `Metadata`, `AnalysisResponse`
- `app/services/transcription.py` — `TranscriptionService` usando Whisper modelo `tiny`
- `app/services/analyser.py` — `AnalyserService` con análisis heurístico (sentimiento, score, objeciones)

### Capacidades actuales
- Transcripción real de audio usando OpenAI Whisper (modelo tiny para rapidez en MVP)
- Mapeo automático de rutas Docker → Windows (`map_dockered_path`)
- Análisis heurístico de sentimiento (positivo/neutral/negativo por keywords)
- Score de probabilidad de venta (0-100, basado en keywords)
- Detección de objeciones (precio alto, competencia, indecisión)
- Generación de resumen (primeros 150 chars del transcript)
- Siguiente acción sugerida (condicional por score y sentimiento)
- Dependencias: `fastapi`, `uvicorn`, `pydantic`, `openai-whisper`, `torch`, `torchaudio`, `ffmpeg-python`, `python-multipart`

### Integración con Laravel
- Laravel (`AIWorkerService.php`) → HTTP POST → `http://host.docker.internal:8001/process-call`
- El mapeo de campos entre el contrato Python y el formato interno de Laravel está resuelto en `AIWorkerService.php`

## 4. Endpoints Listos para Consumo (Desde Android)
El servidor expone estas rutas principales bajo `http://<IP-SERVIDOR>:8000/api/`:
- `POST /auth/login`: Sin protección. Retorna validación criptográfica (`Hash::check`) y emite el Bearer Token.
- `POST /calls`: Protegido (`auth:sanctum`). Registra la metadata inicial de una llamada terminada.
- `POST /calls/{call}/audio`: Protegido (`auth:sanctum`). Recibe el archivo físico de audio y detona asíncronamente el Job de IA via Worker.
- `POST /calls/{call}/reprocess`: Protegido (`auth:sanctum`). Re-encola a la fuerza el Job de análisis.

## 5. Panel Web Administrativo
Rutas web protegidas por middleware `auth`:
- `GET /` → Redirige a reportes
- `GET /reports` → Listado de llamadas con filtros (vendedor, cliente, fecha, estado)
- `GET /reports/{call}` → Detalle: metadata, audio, transcript, análisis comercial
- `POST /reports/{call}/reprocess` → Re-encola análisis IA
- `GET /reports/{call}/audio` → Streaming de audio
- `GET /vendedores` → Listado de vendedores
- `GET /vendedores/create` + `POST /vendedores/store` → Crear vendedor + dispositivo
- `GET /vendedores/{id}/edit` + `PUT /vendedores/{id}/update` → Editar vendedor

**Ruta `/settings` registrada y funcional** bajo el grupo `prefix('settings')` con middleware `auth` en `web.php`.

## 6. Pendientes Críticos para Piloto

### Panel Web
Todos los pendientes del panel web están resueltos:
- [x] Ruta `/settings` registrada en `web.php`
- [x] DataTables en reportes y vendedores
- [x] Exportación a Excel en listados
- [x] Loader + deshabilitar botón en llamadas AJAX

### Validación (bloqueantes reales)
1. Confirmar modelo exacto del celular corporativo (Fase 0 nunca completada formalmente)
2. Ejecutar flujo E2E completo con dispositivo real
3. Validar que FFmpeg está instalado en el equipo donde corre Python-AI
4. Agregar logging detallado en Android (requiere acceso físico al equipo)

### Limpieza completada
- [x] `services/ai-worker/` eliminada — solo existe `services/python-ai/` como servicio IA activo.
- [x] `backend_tmp/` eliminada el 2026-04-24 — era scaffold vacío de Laravel 8.75 sin código propio ni referencia en ningún flujo.
