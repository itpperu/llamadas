# DECISIONS.md

## D-001
### Título
Laravel como backend principal

### Estado
Aceptada

### Motivo
El equipo ya domina PHP y Laravel. Además, los reportes web también deben desarrollarse en Laravel para mantener un stack homogéneo.

---

## D-002
### Título
Excluir WhatsApp del alcance actual

### Estado
Aceptada

### Motivo
El proyecto se enfocará exclusivamente en llamadas comerciales para reducir alcance y ejecutar más rápido el MVP.

---

## D-003
### Título
No usar SIP, Twilio ni softphone

### Estado
Aceptada

### Motivo
Se busca minimizar costos y evitar complejidad de telefonía IP en esta fase. Las llamadas ocurrirán con el teléfono normal del equipo corporativo.

---

## D-004
### Título
Usar grabación nativa del dispositivo

### Estado
Aceptada

### Motivo
La app Android no debe intentar grabar llamadas por su cuenta. El sistema se apoyará en el mecanismo nativo o permitido por el modelo del equipo corporativo.

---

## D-005
### Título
Todos los equipos serán del mismo modelo

### Estado
Aceptada

### Motivo
Esto reduce la variabilidad técnica y permite construir un flujo operativo más estable para asociación de llamadas y audios.

---

## D-006
### Título
Servicio IA en Python desacoplado

### Estado
Aceptada

### Motivo
Laravel orquesta, persiste y reporta; Python procesa transcripción y análisis. Esto reduce acoplamiento y facilita evolución futura del motor IA.

---

## D-007
### Título
Arquitectura de captura y sincronización

### Estado
Aceptada

### Motivo
La llamada no será controlada en tiempo real por la plataforma. El enfoque será detectar, registrar, sincronizar y procesar después del evento.

---

## D-008
### Título
MySQL como base relacional inicial

### Estado
Aceptada

### Motivo
Es suficiente para el MVP, bien soportada por Laravel y fácil de operar.

---

## D-009
### Título
Prioridad del MVP: captura antes que IA

### Estado
Aceptada

### Motivo
No tiene sentido sofisticar el análisis si la llamada y el audio no quedan bien registrados.

---

## D-010
### Título
Ejecución del Backend vía Docker Compose

### Estado
Aceptada

### Motivo
Estandarizar el entorno de desarrollo y futura producción en Windows usando contenedores (Nginx, PHP, MySQL, Worker), evitando configuraciones locales de XAMPP.

---

## D-011
### Título
Autenticación de API mediante Laravel Sanctum

### Estado
Aceptada

### Motivo
Protección estandarizada de endpoints API usando tokens estáticos (`Bearer`) que el dispositivo móvil conservará.

---

## D-012
### Título
Call Up como grabador oficial del dispositivo corporativo

### Estado
Aceptada (2026-04-25)

### Motivo
HyperOS 2.0 / Android 16 en el Xiaomi Redmi 14 Pro no expone una API estable para grabar llamadas desde una app de terceros. Tras evaluación, Call Up (com.callapp.contacts) graba de forma confiable en `/storage/emulated/0/Music/CallAppRecording` con el patrón de nombre `recording-<numero>_<fecha>_<hora>_..._<id>.mp3`. La app corporativa solo necesita leer esa carpeta — no graba por sí misma (alineado con D-004).

---

## D-013
### Título
UUID del dispositivo como mecanismo de onboarding

### Estado
Aceptada (2026-04-25)

### Motivo
Cada equipo corporativo expone su UUID en la pantalla de login (botón "Ver UUID"). El administrador registra el UUID contra el vendedor en el panel web, y desde ese momento el login solo requiere validar el UUID contra el backend. Reduce fricción operativa y evita compartir credenciales por equipo.

---

## D-014
### Título
Compresión de audio pre-upload con MediaCodec (umbral 1 MB)

### Estado
Aceptada (2026-04-25)

### Motivo
Reducir tráfico móvil y tiempo de subida para llamadas largas sin sacrificar calidad útil para transcripción. `AudioCompressor.kt` transcodifica a AAC mono 32 kbps (suficiente para voz) usando MediaCodec/MediaMuxer cuando el archivo original supera 1 MB. Audios cortos pasan tal cual.

---

## D-015
### Título
Workers WorkManager promovidos a foreground service explícitamente en HyperOS

### Estado
Aceptada (2026-05-01)

### Motivo
HyperOS 2.0 (MIUI agresivo) cancela WorkManager workers de larga duración si corren como non-expedited. Para `SyncCallWorker` (espera hasta 53 s buscando grabación) y `SyncAudioWorker` (compresión + upload), se llama a `setForeground(getForegroundInfo())` al inicio de `doWork()`. Adicionalmente se re-declara `androidx.work.impl.foreground.SystemForegroundService` en el manifest con `foregroundServiceType="dataSync"` y `tools:node="merge"` para que Android 14+ acepte la promoción.

### Implicancia
Cualquier nuevo Worker que necesite correr más de unos pocos segundos en HyperOS debe seguir el mismo patrón. Documentado en KI-011.

---

## D-016
### Título
Whisper modelo `base` con español forzado y parámetros anti-alucinación

### Estado
Aceptada (2026-05-01)

### Motivo
`tiny` produce alucinaciones en clips comerciales cortos (texto en español inventado, caracteres de reemplazo). `base` (~74 MB, RAM ~250 MB) duplica accuracy con costo aceptable de CPU. Adicionalmente:
- `language="es"` evita falsa detección de idioma.
- `temperature=0.0` + `condition_on_previous_text=False` + `no_speech_threshold=0.6` + `compression_ratio_threshold=2.4` + `logprob_threshold=-1.0` reducen alucinación residual.
- `initial_prompt` con vocabulario comercial peruano guía el modelo.

### Implicancia
Servidor de producción tiene 3.6 GB libres → escalable a `small` (~1 GB) con un cambio de una palabra si las alucinaciones residuales molestan en producción.

---

## D-017
### Título
Despliegue productivo en `llamadas.innovationtechnologyperu.com` con HTTPS Let's Encrypt

### Estado
Aceptada (2026-04-25)

### Motivo
Servidor Contabo (161.97.71.74) con stack Docker (Laravel) + Supervisor (Python-AI puerto 8001) + Nginx host como proxy reverso. HTTPS vía Certbot. Permite operación con dispositivos reales en campo bajo dominio público confiable. La app Android usa la URL configurada en login para todas las llamadas a la API.
