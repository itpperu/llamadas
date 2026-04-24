# android-app

Proyecto Android Studio para captura y sincronización de llamadas desde dispositivos corporativos.

## Responsabilidades
- autenticación del vendedor/dispositivo
- lectura del call log
- detección de nuevas llamadas
- asociación de grabaciones nativas
- subida de metadata y audios al backend Laravel
- cola local y reintentos

## Estructura sugerida
- `data/`: API, almacenamiento local, repositorios
- `domain/`: casos de uso
- `ui/`: pantallas
- `services/`: servicios de sincronización
- `receivers/`: receptores o listeners del sistema
- `workers/`: WorkManager
- `utils/`: utilitarios

## Nota
Este módulo se abre directamente desde Android Studio.

## Pruebas del Flujo MVP (App → Laravel)
Para probar todo el ciclo cerrado configurado hasta la Fase 5:

1. **Requisitos Previos:**
   - Asegúrate que el backend Laravel está mapeado a la IP local (ej. `192.168.1.47:8000`) en `build.gradle.kts`.
   - Android Studio debe estar conectado al dispositivo y ejecutando `assembleDebug`.
   - Desactiva el firewall temporalmente si usas un equipo físico, o aplica la regla del puerto 8000.

2. **Login Inicial:**
   - Ingresa cualquier credencial en el `LoginActivity`. Se asociará el Vendedor a un token y Device UUID (`SessionManager`).

3. **Captura Autonóma de Llamadas (Fondo):**
   - No necesitas abrir la app principal. Deja la app corriendo en segundo plano (`(Servicio pasivo...)`).
   - Llama a cualquier número (ej. otro celular en la sala o un IVR local). Transcurridos 5-10 segundos, cuelga.
   - El sistema atrapará automáticamente la llamada y ejecutará `SyncCallWorker`.

4. **Sincronización de Metadata (WorkManager):**
   - El worker genera una solicitud silenciosa `POST /api/calls`.
   - Si no hay red, la cola esperará. Si hay red, impactará en Laravel y Laravel contestará un `call_id`.
   - *Verificación interna:* El Logcat dirá `Llamada sincronizada con éxito... ID remoto XX`.

5. **Asociación Asistida de Audio (MVP):**
   - Inicia de nuevo la app del MVP y presiona **"Asociar y Subir Audio"**.
   - Usa el selector nativo provisto para localizar la carpeta "Recordings" del celular y seleccionar el `.mp3` o `.m4a`.

6. **Subida final de Audio:**
   - En automático al retornar del selector de archivos (SAF), la app encola a `SyncAudioWorker`.
   - Este Worker copiará el archivo para enviar su versión `multipart/form-data` en POST `/api/calls/{backendCallId}/audio`.
   - Si hubo éxito, se marcará totalmente completa la gestión.
   - *Consideración:* Si eliminas el archivo nativo desde la galería y su subida falla, la app restablecerá la asociación a nulo para que reintentes sin dañar la cola operativa.
