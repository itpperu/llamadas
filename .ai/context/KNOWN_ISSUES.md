# KNOWN_ISSUES.md

## KI-001
### Problema
Restricciones Android sobre acceso a grabaciones

### Impacto
Puede impedir automatización total del proceso de asociación de audio.

### Mitigación
Se adoptó flujo asistido (SAF - Storage Access Framework). El vendedor selecciona manualmente el archivo de grabación. Documentado en PILOT_GUIDE.md como riesgo #2.

### Estado
Mitigado con workaround. No hay automatización completa.

---

## KI-002
### Problema
Desfase entre la hora registrada en call log y la hora de creación del archivo de audio

### Impacto
Puede producir asociaciones incorrectas en el flujo asistido.

### Mitigación
Se confía en la selección manual del vendedor. Para fases futuras: usar ventana de tolerancia, duración, número y confirmación automatizada.

### Estado
Aceptado como riesgo del MVP.

---

## KI-003
### Problema
Conectividad móvil inestable

### Impacto
La app puede no subir metadata o audio inmediatamente.

### Mitigación
Implementado: cola local en Room (AppDatabase + CallDao) + WorkManager con reintentos automáticos (SyncCallWorker, SyncAudioWorker).

### Estado
Resuelto.

---

## KI-004
### Problema
Archivos muy grandes o formatos variables

### Impacto
Puede afectar tiempos de subida, almacenamiento y procesamiento IA. Llamadas >45 min pueden causar timeout.

### Mitigación
- Validación de MIME/type en UploadAudioRequest.
- Compresión cliente vía `AudioCompressor.kt` (MediaCodec → AAC mono 32 kbps) cuando el archivo supera 1 MB.
- Timeout de 60 s en AIWorkerService.

### Estado
Resuelto (2026-05-01).

---

## KI-005
### Problema
Errores de transcripción (modelo Whisper tiny)

### Impacto
El análisis comercial puede ser incorrecto por bajo accuracy del modelo tiny.

### Mitigación
Guardar transcript bruto, permitir reproceso (RequestReprocessAction). El modelo puede escalarse a `base` o `small` cambiando un parámetro en TranscriptionService.

### Estado
Aceptado para MVP. Escalable.

---

## KI-006
### Problema
Llamadas sin audio asociado

### Impacto
Se pierde valor para análisis IA.

### Mitigación
Estado `audio_pendiente` permite flujo operativo de recuperación. El vendedor puede reintentar la asociación asistida en cualquier momento.

### Estado
Resuelto con flujo asistido.

---

## KI-007
### Problema
Ruta /settings no registrada en web.php

### Impacto
El panel de diagnóstico del sistema (SettingsController) existe pero es inaccesible desde el navegador.

### Mitigación
Ruta registrada en web.php bajo el grupo settings con middleware auth.

### Estado
Resuelto (2026-04-03).

---

## KI-008
### Problema
Carpeta services/ai-worker obsoleta coexiste con services/python-ai

### Impacto
Confusión para futuros agentes o desarrolladores sobre cuál es el servicio IA activo.

### Mitigación
Carpeta services/ai-worker eliminada del repositorio.

### Estado
Resuelto (2026-04-03).

---

## KI-009
### Problema
Análisis heurístico limitado por keywords

### Impacto
El AnalyserService usa búsqueda simple de palabras clave. No distingue contexto ni negación compleja ("no me interesa" podría detectar "interesa" como positivo).

### Mitigación
Suficiente para MVP. En fases futuras, integrar un modelo de lenguaje (GPT, Claude) para análisis contextual.

### Estado
Aceptado para MVP.

---

## KI-010
### Problema
Directorio `backend_tmp/` en la raíz del proyecto

### Impacto
Confusión para agentes y desarrolladores sobre si ese directorio era el backend activo.

### Causa raíz
Scaffold vacío de Laravel 8.75 generado al inicio del proyecto. No tenía `app/`, `database/` ni `routes/` propios. Nunca fue referenciado por ningún script, Docker Compose ni archivo de configuración.

### Resolución
Eliminado el 2026-04-24. El backend activo es exclusivamente `backend/` (Laravel 11, dockerizado).

### Estado
Resuelto (2026-04-24).

---

## KI-011
### Problema
Subida automática de audio fallaba en HyperOS 2.0 (Xiaomi Redmi 14 Pro)

### Impacto
El vendedor tenía que pulsar manualmente el botón "Asociar" para cada llamada. Bloqueante crítico del Sprint 4.

### Causa raíz
Doble defecto en la app Android:
1. `SyncCallWorker` y `SyncAudioWorker` se encolaban como expedited con fallback a non-expedited. En HyperOS, si Android quedaba sin cuota expedita, el worker corría sin foreground service y el sistema lo mataba durante las esperas/compresión.
2. Al añadir `setForeground(getForegroundInfo())` con type `FOREGROUND_SERVICE_TYPE_DATA_SYNC`, WorkManager intentaba promover su `SystemForegroundService` interno, que **no estaba declarado en el manifest** con ese type. Android 14+ rechaza con `IllegalArgumentException` y mata el proceso.

### Resolución (2026-05-01)
- `setForeground(getForegroundInfo())` al inicio de `doWork()` en ambos workers (SyncCallWorker.kt:46-47, SyncAudioWorker.kt:46-47).
- Re-declaración de `androidx.work.impl.foreground.SystemForegroundService` en `AndroidManifest.xml` con `foregroundServiceType="dataSync"` y `tools:node="merge"`.

### Estado
Resuelto. Validado en producción con llamada real (ID 47): subida automática completada en ~12 s tras colgar.

---

## KI-012
### Problema
Modelo Whisper `tiny` produce alucinaciones en audio comercial corto

### Impacto
Transcripts inventados ("¿Por qué sería? Omelo importante�", caracteres de reemplazo, frases random sin relación al audio). Daña confiabilidad del análisis comercial.

### Causa raíz
1. `tiny` tiene WER ~30% en español y baja confianza en clips de pocos segundos / bajo bitrate.
2. Sin `language="es"`, intenta auto-detectar y falla con audios cortos comprimidos a 12-32 kbps.

### Resolución (2026-05-01)
- Subido `tiny` → `base` en `services/python-ai/app/main.py:11` (~74 MB, WER ~20%).
- En `transcription.py:18-31`: `language="es"`, `task="transcribe"`, `initial_prompt` comercial, `temperature=0.0`, `condition_on_previous_text=False`, `no_speech_threshold=0.6`, `compression_ratio_threshold=2.4`, `logprob_threshold=-1.0`.

### Estado
Mitigado. Quedan pequeñas alucinaciones residuales en clips muy cortos (<10 s). Si molestan, escalable a `small` con un cambio de palabra (RAM disponible suficiente).

---

## KI-013
### Problema
Puerto 8001 (Python-AI) expuesto a internet en el servidor de producción

### Impacto
Exposición innecesaria del servicio interno. Logs llenos de scans (`/etc/passwd`, `/mcp`, `proxiesfood.com`). Riesgo de explotación si se descubre vulnerabilidad en uvicorn/FastAPI/Whisper.

### Causa raíz
Uvicorn arrancado con `--host 0.0.0.0` sin firewall delante; el puerto 8001 no fue restringido en UFW.

### Mitigación recomendada (pendiente)
Cualquiera de estas dos:
- Cambiar comando en `/etc/supervisor/conf.d/python-ai.conf`: `--host 127.0.0.1` (solo localhost) y configurar Docker para acceder vía `host.docker.internal` o IP del bridge.
- Bloquear con UFW: `ufw deny 8001/tcp` (verificando primero que Docker accede por interfaz interna, no por la pública).

### Estado
Pendiente. No urgente pero importante.

---

## KI-014
### Problema
Archivos de runtime trackeados en git generan divergencia recurrente con producción

### Impacto
Cada `git pull` en producción reporta cientos de archivos modificados/borrados (`.env`, `bootstrap/cache/*`, `storage/app/audios/*`, `storage/framework/cache|sessions|views/*`, `storage/logs/laravel.log`). Forzó un `git reset --hard` en el servidor el 2026-05-01.

### Causa raíz
El `.gitignore` del `backend/` no excluye los paths estándar de Laravel para runtime, ni los audios subidos por usuarios. Quedaron versionados desde la primera subida.

### Mitigación recomendada (pendiente)
1. Editar `backend/.gitignore` para añadir: `.env`, `/storage/*`, `!/storage/.gitkeep`, `/bootstrap/cache/*`, `!/bootstrap/cache/.gitkeep`.
2. `git rm -r --cached backend/storage backend/bootstrap/cache backend/.env` y commit.
3. Crear placeholders `.gitkeep` en las carpetas necesarias.

### Estado
Pendiente. Tarea de limpieza con bajo riesgo si se hace en local y se prueba antes de pushear.
