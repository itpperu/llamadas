# TASKS.md

## Roadmap del proyecto

### Fase 0 - Descubrimiento técnico del equipo
- [x] Confirmar modelo exacto del celular corporativo (Xiaomi Redmi 14 Pro)
- [x] Confirmar versión Android (HyperOS 2.0 / Android 16 / API 36)
- [x] Validar acceso a call log (CallLogReader.kt implementado)
- [x] Validar disponibilidad de grabación nativa (Call Up adoptado — D-012)
- [x] Detectar ruta o mecanismo de acceso a la grabación (`/storage/emulated/0/Music/CallAppRecording`)
- [x] Probar asociación llamada ↔ audio (RecordingFinder.kt automático por número + ventana de tiempo)
- [x] Documentar resultados técnicos del modelo específico (DECISIONS.md D-012, KI-011)

### Fase 1 - Base Android MVP
- [x] Crear proyecto Android Studio (com.grabacionllamada.app)
- [x] Implementar login de vendedor/dispositivo (LoginActivity + AuthRepository.kt + SessionManager.kt)
- [x] Detectar llamadas nuevas (PhoneStateReceiver.kt)
- [x] Normalizar número (PhoneNumberNormalizer.kt)
- [x] Crear cola local (Room: AppDatabase.kt, CallDao.kt, CallEntity.kt)
- [x] Registrar metadata en API Laravel (SyncCallWorker.kt → POST /api/calls)
- [x] Implementar adjuntar o ubicar audio (flujo asistido vía SAF en MainActivity)
- [x] Subir audio con reintentos (SyncAudioWorker.kt → POST /api/calls/{id}/audio)

### Fase 2 - Base Laravel
- [x] Crear proyecto Laravel (Docker Compose: app, worker, nginx, db)
- [x] Configurar autenticación de app móvil (Sanctum Bearer Tokens)
- [x] Crear migraciones iniciales (9 migraciones)
- [x] Crear modelos base (8 modelos Eloquent)
- [x] Crear controladores API (AuthController, CallController)
- [x] Crear servicios/actions base (3 Actions + AIWorkerService)
- [x] Crear estructura de reportes web (ReportController + VendedorController + SettingsController + Blade views)

### Fase 3 - Almacenamiento y estados
- [x] Guardar audios de forma segura (UploadAudioAction.php + Storage disk local)
- [x] Versionar estados de llamada (8 estados: registrada → analizada)
- [x] Crear logs de sincronización (tabla logs_sincronizacion + modelo LogSincronizacion)
- [x] Validar integridad de archivo (hash cruzado md5 cliente vs servidor en UploadAudioAction)
- [x] Implementar reproceso manual (RequestReprocessAction + ruta web y API)

### Fase 4 - IA
- [x] Definir contrato JSON Laravel ↔ Python (CONTRACT_AI.md)
- [x] Implementar transcripción (services/python-ai: Whisper tiny via TranscriptionService)
- [x] Implementar análisis de sentimiento (services/python-ai: AnalyserService heurístico)
- [x] Implementar intención comercial (derivado del score en AIWorkerService.php)
- [x] Implementar score de venta (AnalyserService: probabilidad_venta 0-100)
- [x] Implementar extracción de objeciones (AnalyserService: detección por keywords)
- [x] Implementar siguiente acción sugerida (AnalyserService: reglas condicionales)
- [x] Integrar Laravel ↔ Python (AIWorkerService.php → HTTP POST → FastAPI /process-call)
- [x] Mapeo de rutas Docker ↔ Windows (map_dockered_path en main.py)

### Fase 5 - Reportes
- [x] Reporte por vendedor (filtro en ReportController.index)
- [x] Reporte por cliente (filtro en ReportController.index)
- [x] Reporte por rango de fechas (filtros fecha_desde/fecha_hasta)
- [x] Vista detalle de llamada (reports/show.blade.php)
- [x] Reproducción de audio (streamAudio() en ReportController)
- [x] Visualización de transcript (en vista show)
- [x] Visualización del análisis (en vista show)
- [x] Implementar DataTables en listados (reportes + vendedores con paginación, búsqueda y ordenamiento)
- [x] Implementar exportación a Excel (DataTables Buttons + JSZip client-side + CSV server-side fallback)
- [x] Reportes agregados por vendedor (reports/vendors con KPIs, sentimiento global, DataTables y Excel)
- [x] Loader + deshabilitar botón en llamados AJAX (overlay global con spinner en todos los formularios)

### Fase 6 - Operación
- [x] Seguridad básica (Sanctum + validaciones cruzadas de identidad)
- [x] Worker de colas en Docker (laravel_worker con queue:work + backoff progresivo)
- [x] Ruta /settings registrada en web.php (panel de diagnóstico accesible)
- [x] Logging técnico en Laravel (ProcessCallAIJob con timing, clasificación de errores, log a BD)
- [x] Monitoreo de colas (artisan system:queue-status + dashboard web en /settings)
- [x] Políticas de retención (artisan system:purge --days=N --dry-run)
- [x] Copias de respaldo (artisan system:backup con dump SQL o CSV fallback)
- [x] Procedimientos de soporte (OPERATIONS.md completo)
- [ ] Logging en Android (pendiente: requiere acceso al celular)

### Fase 7 - Implementación en producción (iniciada 2026-04-25, cerrada 2026-05-01)
- [x] Deploy backend Laravel en servidor Linux (161.97.71.74) vía Docker
- [x] Deploy servicio Python-AI con Supervisor
- [x] Configurar Nginx del servidor como proxy reverso al subdominio llamadas.innovationtechnologyperu.com
- [x] Activar HTTPS con Certbot (Let's Encrypt)
- [x] App Android compilada y desplegada en Xiaomi Redmi 14 Pro
- [x] Call Up instalado como grabador de llamadas (Music/CallAppRecording)
- [x] Detección automática de llamadas vía TelephonyCallback (CallMonitorService)
- [x] Detección automática del archivo de audio (RecordingFinder → Music/CallAppRecording)
- [x] Compresión de audio pre-upload (AudioCompressor — solo archivos > 1MB)
- [x] Limpieza automática del archivo local tras subida exitosa
- [x] **Subida automática de audio sin intervención del vendedor** — resuelto 2026-05-01 (KI-011, D-015): `setForeground` explícito + re-declaración de `SystemForegroundService` en manifest. Validado E2E con llamada ID 47.
- [x] Transcripción IA en español coherente — resuelto 2026-05-01 (KI-012, D-016): Whisper `base` + `language="es"` + parámetros anti-alucinación.

### Fase 8 - Endurecimiento de producción (pendiente)
- [ ] Restringir puerto 8001 (Python-AI) a localhost o vía UFW (KI-013)
- [ ] Limpiar `.gitignore` del backend para destrackear `storage/`, `bootstrap/cache/`, `.env`, audios (KI-014)
- [ ] Logging técnico en Android (Timber + envío opcional al backend)
- [ ] Evaluar subida de Whisper a `small` si las alucinaciones residuales molestan en operación real
- [ ] Documentar procedimiento de instalación de Call Up en nuevos dispositivos del piloto

## Prioridad actual
Sprint 4 cerrado. Próximo sprint: Fase 8 (endurecimiento) cuando el negocio confirme.
