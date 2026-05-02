# CURRENT_SPRINT.md

## Sprint actual
Sprint 4 — **CERRADO 2026-05-01**

## Objetivo
Validar el flujo completo E2E con dispositivo real en producción y resolver la subida automática de audio.

## Resultado
✅ **Objetivo cumplido.** Flujo end-to-end funcional sin intervención manual del vendedor.

## Validación E2E (llamada de prueba ID remoto 47)
1. ✅ Llamada entrante detectada vía `TelephonyCallback` (CallMonitorService).
2. ✅ Metadata sincronizada al backend Laravel en 1.2 s.
3. ✅ Búsqueda automática de grabación: encontrada en `Music/CallAppRecording` por coincidencia de número + ventana de tiempo.
4. ✅ Audio (32 KB / 22 s) subido automáticamente al backend en ~12 s totales tras colgar.
5. ✅ Transcripción Python-AI en español coherente: *"Hola, ¿qué tal? Sí, sí, sí, acá estoy, vamos en el proyecto"* (con alucinación residual menor: "Punan").
6. ✅ Análisis comercial generado y visible en panel web.

## Bloqueantes resueltos en este sprint
- **KI-011** — Subida automática fallaba en HyperOS por workers sin foreground service. Resuelto con `setForeground()` explícito + re-declaración de `SystemForegroundService` en manifest. Ver D-015.
- **KI-012** — Whisper `tiny` producía alucinaciones. Resuelto subiendo a `base` + `language="es"` + parámetros anti-alucinación. Ver D-016.

## Decisiones registradas en este sprint
- D-012: Call Up como grabador oficial.
- D-013: UUID del dispositivo como mecanismo de onboarding.
- D-014: Compresión audio pre-upload (umbral 1 MB).
- D-015: Workers en foreground service explícito en HyperOS.
- D-016: Whisper `base` + español forzado + anti-alucinación.
- D-017: Despliegue productivo con HTTPS Let's Encrypt.

## Pendientes para el siguiente sprint (Fase 8 — endurecimiento)
1. **🔒 Cerrar puerto 8001 al exterior** (KI-013). Logs muestran scans hostiles.
2. **🧹 Limpiar `.gitignore` del backend** (KI-014). Evita divergencias recurrentes en producción.
3. **📈 Evaluar Whisper `small`** si las alucinaciones residuales (tipo "Punan") afectan operación real. RAM disponible suficiente.
4. **📝 Logging Android** estructurado (Timber + envío opcional al backend).
5. **📘 Procedimiento de instalación** de Call Up + permisos HyperOS para nuevos dispositivos del piloto.

## Criterio de éxito (cumplido)
- [x] Una llamada real completa el ciclo: registrada → audio_subido → analizada
- [x] El panel web muestra la llamada con transcript, análisis y reproducción de audio
- [x] Los reportes usan DataTables y permiten exportar a Excel
- [x] La página de diagnóstico (/settings) muestra estado del sistema
- [x] Subida automática de audio sin intervención del vendedor
- [x] Transcripción en español coherente con el audio
