# CURRENT_SPRINT.md

## Sprint actual
Sprint 4 - Implementación y Piloto en Producción

## Objetivo
Validar el flujo completo E2E con dispositivo real en producción y resolver la subida automática de audio.

## Contexto
Las fases 1–5 están sustancialmente completas:
- Backend Laravel dockerizado y funcional (app, worker, nginx, db)
- App Android con captura pasiva, cola local y sincronización via WorkManager
- Microservicio Python-AI con Whisper (transcripción) + análisis heurístico (sentimiento, score, objeciones)
- Panel web con reportes, detalle de llamada, audio, transcript e integración IA
- Gestión CRUD de vendedores

## Entregables esperados
- Panel web pulido con DataTables, exportación Excel y loaders AJAX
- Ruta /settings accesible para diagnóstico del sistema
- Flujo E2E validado: llamada real → Android → Laravel → Python-AI → Panel
- Documentación alineada con el código real

## Tareas completadas en este sprint
- [x] Registrar ruta /settings en web.php (verificado en routes/web.php — grupo settings con middleware auth)
- [x] Implementar DataTables en listados de reportes y vendedores
- [x] Implementar exportación a Excel en cada listado
- [x] Agregar loader + deshabilitación de botón en llamadas AJAX
- [x] Limpiar carpeta services/ai-worker (eliminada — solo existe services/python-ai)

## Tareas completadas en este sprint
- [x] Deploy completo en servidor de producción (Docker + Supervisor + Nginx + HTTPS)
- [x] App Android instalada en Xiaomi Redmi 14 Pro (HyperOS 2.0)
- [x] Call Up instalado como grabador — archivos en Music/CallAppRecording
- [x] Detección automática de llamadas vía TelephonyCallback
- [x] RecordingFinder localiza grabaciones automáticamente por número y timestamp
- [x] Compresión de audio pre-upload (AudioCompressor — archivos > 1MB)
- [x] Eliminación automática del archivo local tras subida exitosa
- [x] UUID del dispositivo como mecanismo de onboarding (botón "Ver UUID" en login)
- [x] URL configurable desde la pantalla de login de la app
- [x] Botón "Asociar" por fila en el log de llamadas
- [x] Flujo completo validado: llamada → registro → análisis IA → panel web

## Tareas pendientes (bloqueantes para cierre de sprint)
- [ ] **Subida automática de audio** — SyncAudioWorker se cancela en HyperOS antes de completar. Workaround: botón "Asociar" manual. Requiere depuración del ciclo de vida de WorkManager en HyperOS.

## Tareas completadas recientemente
- [x] Microservicio Python-AI implementado (services/python-ai/)
- [x] Integración real Laravel ↔ Python (AIWorkerService.php → /process-call)
- [x] Endurecimiento de seguridad (idempotencia, validaciones cruzadas)
- [x] Guía de piloto documentada (PILOT_GUIDE.md)
- [x] Documentación del proyecto actualizada (TASKS.md sincronizado)

## Riesgos del sprint
- El modelo de celular no sea confirmado antes del piloto
- Problemas de firewall entre Docker y Python local (puerto 8001)
- Archivos de audio demasiado grandes (>45min) causando timeout en Whisper

## Criterio de éxito
El sprint se considera exitoso si:
- Una llamada real de prueba completa el ciclo: registrada → audio_subido → analizada
- El panel web muestra la llamada con transcript, análisis y reproducción de audio
- Los reportes usan DataTables y permiten exportar a Excel
- La página de diagnóstico (/settings) muestra estado del sistema
