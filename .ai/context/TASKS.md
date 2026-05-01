# TASKS.md

## Roadmap del proyecto

### Fase 0 - Descubrimiento técnico del equipo
- [ ] Confirmar modelo exacto del celular corporativo
- [ ] Confirmar versión Android
- [x] Validar acceso a call log (CallLogReader.kt implementado)
- [x] Validar disponibilidad de grabación nativa (flujo asistido SAF adoptado)
- [x] Detectar ruta o mecanismo de acceso a la grabación (decisión: selector de archivos SAF)
- [x] Probar asociación llamada ↔ audio (flujo asistido implementado en SyncAudioWorker.kt)
- [ ] Documentar resultados técnicos del modelo específico

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

### Fase 7 - Implementación en producción (iniciada 2026-04-25)
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
- [ ] **PENDIENTE: Subida automática de audio sin intervención del vendedor** — el RecordingFinder encuentra el archivo correctamente pero el SyncAudioWorker se cancela antes de completar la subida. Se requiere depuración adicional del ciclo de vida del WorkManager en HyperOS.

## Prioridad actual
1. Resolver subida automática de audio (SyncAudioWorker cancelado en HyperOS)
2. Documentar modelo de celular corporativo — Xiaomi Redmi 14 Pro (HyperOS 2.0, Android 16)
3. Validar flujo E2E completo con llamada real de producción
