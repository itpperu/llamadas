# TECH_STACK.md

## Stack aprobado

### Android app
- Kotlin
- Android Studio
- Retrofit/OkHttp para API
- WorkManager para tareas en background
- Room opcional para persistencia local y cola de sincronización
- Android call log APIs según permisos y compatibilidad del equipo

### Backend
- PHP 8.2+
- Laravel 11
- Blade para panel y reportes
- Eloquent ORM
- Form Requests para validación
- Jobs/Queues para procesos pesados
- Scheduler para tareas recurrentes
- Sanctum o autenticación basada en tokens para la app
- usar jquery para la comunicacion asincrona con el servidor
- usar datatables para los reportes
- cada vez que se realice un llamado con ajax se debe mostrar un loader y deshabilitar el boton
- cada listado debe exportarse a excel.

### Base de datos
- MySQL 8+
- migraciones Laravel como fuente oficial de estructura
- índices orientados a filtros por vendedor, cliente, fecha y estado

### IA
- Python 3.11+
- servicio desacoplado
- API interna simple o worker de integración
- salida JSON estructurada

### Infraestructura
- Ubuntu Server
- Nginx
- PHP-FPM
- Supervisor
- Redis opcional para colas
- storage local o compatible S3

## Principios del stack
- usar herramientas dominadas por el equipo
- evitar costos altos innecesarios
- mantener la lógica principal en Laravel
- aislar IA fuera del backend principal
- permitir mantenimiento barato y escalable

## Decisiones técnicas derivadas
- no usar SIP
- no usar Twilio
- no usar softphone en el MVP
- no mezclar Android con backend
- no convertir Python en backend principal

## Estructura física real del repo
```text
raiz/
├── .ai/
├── android-app/
├── backend/
├── services/
│   └── python-ai/        ← servicio de IA (FastAPI + Whisper)
├── docs/
└── scripts/
```

## Distribución de responsabilidades
- `android-app/`: captura y sincronización (Kotlin, WorkManager, Room)
- `backend/`: API, panel, reportes y orquestación (Laravel 11, Docker)
- `services/python-ai/`: transcripción con Whisper y análisis heurístico (FastAPI, puerto 8001)
- `.ai/`: memoria operativa y reglas del proyecto

