# Python AI Microservice

Microservicio de FastAPI para la transcripción y análisis de llamadas comerciales.

## Requisitos
- Python 3.10+
- **FFmpeg instalado en el sistema:** Whisper requiere esta herramienta para manejar archivos de audio.

### 🛠️ Guía de instalación de FFmpeg (Windows)
Si al procesar una llamada ves el error `[WinError 2] El sistema no puede encontrar el archivo especificado`, es porque FFmpeg no está en tu PATH. Sigue estos pasos:

1.  **Descarga:** Entra a [gyan.dev/ffmpeg/builds](https://www.gyan.dev/ffmpeg/builds/ffmpeg-release-essentials.zip) y descarga el archivo comprimido.
2.  **Extraer:** Descomprime el archivo en una carpeta de fácil acceso, por ejemplo: `C:\ffmpeg`.
3.  **Configurar PATH:**
    *   Presiona la tecla `Inicio` y escribe: *"Editar las variables de entorno del sistema"*.
    *   Haz clic en el botón inferior **Variables de entorno**.
    *   En la lista **Variables del sistema**, selecciona la que dice `Path` y haz clic en **Editar**.
    *   Haz clic en **Nuevo** y añade la ruta completa a la carpeta `bin` de FFmpeg (ej: `C:\ffmpeg\bin`).
    *   Haz clic en **Aceptar** en todas las ventanas.
4.  **Reiniciar Terminal:** Cierra la terminal actual de Python y abre una **NUEVA** para que Windows reconozca el cambio. Prueba escribiendo `ffmpeg -version` para confirmar.

## Instalación

1. Crear entorno virtual (opcional pero recomendado):
```bash
python -m venv venv
.\venv\Scripts\activate
```

2. Instalar dependencias:
```bash
pip install -r requirements.txt
```

## Guía de Prueba End-to-End (E2E)

Sigue estos pasos para validar que el audio de la llamada fluye hasta el análisis de IA.

### 1. Preparación del Entorno
- Asegúrate de que **Docker** esté corriendo (Backend Laravel).
- Inicia el **Microservicio Python** en el puerto 8001: 
  `python -m uvicorn app.main:app --host 0.0.0.0 --port 8001 --reload`
- En una terminal aparte, inicia el **Worker de Laravel** dentro del proyecto `backend`:
  `docker compose exec app php artisan queue:work`

### 2. Ejecución del Flujo
1. **Subir Llamada (Metadata):** Envía un `POST /api/calls` desde Postman para crear un registro en estado `audio_pendiente`. Obtén el `call_id`.
2. **Subir Audio (Binario):** Envía el `.mp3` al endpoint `POST /api/calls/{id}/audio`.
3. **Validación de Disparo:**
   - En el log de Laravel (Docker) verás: `Enviando llamada X a servicio AI`.
   - En la terminal de Python verás: `Recibida petición para procesar la llamada ID: X`.
   - Verás el log de Whisper transcribiendo.
4. **Verificación de Resultados:**
   - La tabla `llamadas` debe tener ahora el `transcript_text`.
   - La tabla `analisis_llamada` debe tener una fila nueva vinculada al `call_id`.

### Errores Probables y Diagnóstico
| Error | Causa Probable | Solución |
| --- | --- | --- |
| `failed to connect to host.docker.internal` | Laravel no ve al host | Asegúrate que el puerto 8001 esté abierto en el firewall de Windows. |
| `404 Not Found (Audio)` | El mapper de rutas falló | Revisa en `main.py` si la ruta `C:\xampp\...` que imprime Python es correcta. |
| `FFmpeg not found` | No instalaste FFmpeg | Descarga e instala FFmpeg en tu sistema y añádelo al PATH. |
| `Transcript empty` | Audio muy corto o ruido | Prueba con un audio de voz clara de al menos 5 segundos. |
