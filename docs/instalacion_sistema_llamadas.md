# Instalación del Sistema de Llamadas Comerciales

**Dominio:** llamadas.innovationtechnologyperu.com  
**Servidor:** Ubuntu | Usuario: root | IP: 161.97.71.74  
**Repositorio:** https://github.com/itpperu/llamadas.git

---

## Prerequisitos del servidor

Antes de comenzar, verificar que el servidor tenga lo siguiente instalado:

```bash
nginx -v          # debe mostrar versión de Nginx
ffmpeg -version   # debe mostrar versión de FFmpeg
python3 --version # debe mostrar Python 3.x
```

Si alguno falta, instalarlo:

```bash
apt-get update
apt-get install -y nginx ffmpeg python3 python3-venv python3-pip
```

---

## Paso 1 — Subir el código a GitHub

Esto se hace **desde tu computadora de desarrollo (Windows)**, no desde el servidor.

Abrir una terminal en la carpeta del proyecto y ejecutar los siguientes comandos uno por uno:

```bash
cd C:/xampp/htdocs/grabacion_llamada
```

Inicializar el repositorio Git local:

```bash
git init
```

Agregar todos los archivos al repositorio (el `.gitignore` ya excluye automáticamente archivos sensibles como `.env`, `vendor/`, audios, etc.):

```bash
git add .
```

Crear el primer commit:

```bash
git commit -m "initial commit"
```

Conectar el repositorio local con GitHub:

```bash
git remote add origin https://github.com/itpperu/llamadas.git
```

Subir el código a GitHub:

```bash
git push -u origin main
```

> Si te pide usuario y contraseña de GitHub, ingresa tus credenciales. Si tienes autenticación en dos pasos activada, necesitas usar un **Personal Access Token** en lugar de la contraseña. Se genera en GitHub → Settings → Developer settings → Personal access tokens.

> Si el repositorio ya existía en GitHub con código previo, ejecutar solo `git push -u origin main`. Si da error de historial divergente: `git push -u origin main --force`.

---

## Paso 2 — Conectarse al servidor

Abrir una terminal y conectarse por SSH:

```bash
ssh root@161.97.71.74
```

Te pedirá la contraseña del servidor. Ingrésala y presiona Enter. Cuando el prompt cambie a algo como `root@servidor:~#` ya estás dentro del servidor.

---

## Paso 3 — Instalar Docker y Docker Compose

Docker es el sistema que corre el backend (Laravel, MySQL, Nginx) en contenedores aislados.

Ejecutar el instalador oficial de Docker:

```bash
curl -fsSL https://get.docker.com | sh
```

Habilitar Docker para que inicie automáticamente cuando el servidor se reinicie:

```bash
systemctl enable docker
systemctl start docker
```

Verificar que Docker quedó instalado correctamente:

```bash
docker --version
docker compose version
```

Debe mostrar algo como:
```
Docker version 26.x.x, build xxxxxxx
Docker Compose version v2.x.x
```

---

## Paso 4 — Instalar Supervisor y dependencias

Supervisor es el programa que mantiene el servicio Python-AI corriendo en todo momento. Si el servicio cae, Supervisor lo reinicia automáticamente.

```bash
apt-get install -y supervisor python3-venv python3-pip git curl openssl
```

Verificar que Supervisor quedó instalado:

```bash
supervisorctl version
```

---

## Paso 5 — Clonar el repositorio en el servidor

Esto descarga el código desde GitHub al servidor. Ejecutar estando conectado por SSH:

```bash
git clone https://github.com/itpperu/llamadas.git /var/www/grabacion_llamada
```

Esto crea la carpeta `/var/www/grabacion_llamada` con todo el código del proyecto.

Verificar que se descargó correctamente:

```bash
ls /var/www/grabacion_llamada
```

Debe mostrar las carpetas: `android-app`, `backend`, `services`, `docs`, `scripts`.

> Si el repositorio es **privado**, Git pedirá usuario y contraseña (o token) de GitHub. Si prefieres no ingresar credenciales cada vez, configurar un Personal Access Token o una clave SSH.

---

## Paso 6 — Crear el archivo .env del backend

El archivo `.env` contiene la configuración del sistema (base de datos, URL, claves). No se sube a GitHub por seguridad, por eso hay que crearlo manualmente en el servidor copiando el archivo de ejemplo:

```bash
cd /var/www/grabacion_llamada/backend
cp .env.example .env
```

Abrir el archivo para editarlo:

```bash
nano .env
```

Modificar los siguientes valores:

| Variable | Valor a colocar |
|---|---|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `http://llamadas.innovationtechnologyperu.com` |
| `DB_PASSWORD` | Una contraseña segura (ej: `Llam4d4s$2026`) |
| `MYSQL_PASSWORD` | La misma contraseña que `DB_PASSWORD` |
| `MAIL_USERNAME` | Tu correo SMTP (para recuperación de contraseña) |
| `MAIL_PASSWORD` | Tu contraseña o app password del correo |
| `MAIL_FROM_ADDRESS` | El correo que aparecerá como remitente |

Para guardar y salir de nano: presionar `Ctrl + X`, luego `Y`, luego `Enter`.

> **Importante:** Guarda la contraseña de base de datos en un lugar seguro. La necesitarás si tienes que hacer una restauración manual.

---

## Paso 7 — Levantar los contenedores Docker

Ingresar a la carpeta del backend:

```bash
cd /var/www/grabacion_llamada/backend
```

Construir las imágenes y levantar todos los contenedores:

```bash
docker compose up -d --build
```

Este comando descarga las imágenes de PHP, MySQL y Nginx, construye la imagen del backend y levanta todo. La primera vez puede tardar **5 a 10 minutos**.

Esperar que termine y verificar que todos los contenedores estén corriendo:

```bash
docker ps
```

Debe mostrar 4 contenedores corriendo:

```
NAMES              STATUS
laravel_nginx      Up
laravel_app        Up
laravel_worker     Up
laravel_mysql      Up (healthy)
```

Si alguno no aparece o dice `Exited`, ver sus logs para diagnosticar:

```bash
docker logs laravel_app --tail=30
```

---

## Paso 8 — Generar la clave de la aplicación y correr migraciones

Generar la clave de seguridad de Laravel (encripta sesiones y cookies):

```bash
docker exec laravel_app php artisan key:generate --force
```

Esperar 20 segundos a que la base de datos termine de inicializarse y luego correr las migraciones (crea todas las tablas en la base de datos):

```bash
sleep 20
docker exec laravel_app php artisan migrate --force
```

Limpiar la caché:

```bash
docker exec laravel_app php artisan config:clear
docker exec laravel_app php artisan route:clear
docker exec laravel_app php artisan view:clear
```

---

## Paso 9 — Configurar el servicio Python-AI

El servicio de inteligencia artificial (transcripción y análisis de llamadas) corre fuera de Docker directamente en el servidor, gestionado por Supervisor.

### 9.1 Crear el entorno virtual de Python

El entorno virtual aísla las dependencias del proyecto Python del resto del sistema:

```bash
cd /var/www/grabacion_llamada/services/python-ai
python3 -m venv venv
```

Esto crea una carpeta `venv/` dentro de `python-ai/`.

### 9.2 Activar el entorno virtual e instalar dependencias

```bash
source venv/bin/activate
```

El prompt cambiará a `(venv) root@servidor:...` indicando que el entorno está activo.

Instalar todas las dependencias del servicio (FastAPI, Whisper, PyTorch, etc.):

```bash
pip install -r requirements.txt
```

> **Advertencia:** Esta instalación puede tardar **10 a 20 minutos** porque descarga PyTorch y los modelos de Whisper, que son archivos de varios GB.

Desactivar el entorno virtual al terminar:

```bash
deactivate
```

### 9.3 Configurar Supervisor para que mantenga el servicio activo

Crear el archivo de configuración de Supervisor:

```bash
nano /etc/supervisor/conf.d/python-ai.conf
```

Pegar el siguiente contenido dentro del archivo. **No incluir las líneas de comillas — son solo formato de este documento:**

```ini
[program:python-ai]
command=/var/www/grabacion_llamada/services/python-ai/venv/bin/uvicorn app.main:app --host 0.0.0.0 --port 8001 --workers 1
directory=/var/www/grabacion_llamada/services/python-ai
user=root
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
stderr_logfile=/var/log/supervisor/python-ai.err.log
stdout_logfile=/var/log/supervisor/python-ai.out.log
environment=PYTHONUNBUFFERED="1"
```

> El archivo debe comenzar con `[program:python-ai]` en la primera línea y terminar con `environment=PYTHONUNBUFFERED="1"`. Sin comillas adicionales.

Guardar y salir: `Ctrl + X`, luego `Y`, luego `Enter`.

Aplicar la nueva configuración e iniciar el servicio:

```bash
supervisorctl reread
supervisorctl update
supervisorctl start python-ai
```

Verificar que el servicio está corriendo:

```bash
supervisorctl status python-ai
```

Debe mostrar:
```
python-ai    RUNNING   pid 12345, uptime 0:00:10
```

---

## Paso 10 — Configurar Nginx para el subdominio

El servidor ya tiene Nginx instalado con otros subdominios configurados. Se agrega uno nuevo para este sistema.

Crear el archivo de configuración del subdominio:

```bash
nano /etc/nginx/sites-available/llamadas.conf
```

Pegar el siguiente contenido dentro del archivo. **No incluir las líneas de comillas:**

```nginx
server {
    listen 80;
    server_name llamadas.innovationtechnologyperu.com;

    client_max_body_size 50M;

    location / {
        proxy_pass         http://127.0.0.1:8000;
        proxy_set_header   Host $host;
        proxy_set_header   X-Real-IP $remote_addr;
        proxy_set_header   X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header   X-Forwarded-Proto $scheme;
        proxy_read_timeout 300s;
        proxy_send_timeout 300s;
        proxy_connect_timeout 60s;
    }
}
```

Guardar y salir: `Ctrl + X`, luego `Y`, luego `Enter`.

Activar el sitio creando un enlace simbólico:

```bash
ln -sf /etc/nginx/sites-available/llamadas.conf /etc/nginx/sites-enabled/llamadas.conf
```

Verificar que la configuración de Nginx no tiene errores:

```bash
nginx -t
```

Debe mostrar:
```
nginx: configuration file /etc/nginx/nginx.conf test is successful
```

Recargar Nginx para aplicar los cambios:

```bash
systemctl reload nginx
```

---

## Paso 11 — Crear usuario administrador del panel

```bash
docker exec laravel_app php artisan tinker --execute="
App\Models\User::create([
    'name'     => 'Administrador',
    'email'    => 'admin@tudominio.com',
    'password' => bcrypt('TuClave2026!'),
    'rol'      => 'admin'
]);
"
```

Reemplazar `admin@tudominio.com` y `TuClave2026!` con los valores reales antes de ejecutar.

---

## Paso 12 — Verificar que todo funciona

Abrir en el navegador:

```
http://llamadas.innovationtechnologyperu.com
```

Debe aparecer la pantalla de login. Ingresar con el usuario creado en el paso anterior.

Verificar el estado de todos los servicios en:

```
http://llamadas.innovationtechnologyperu.com/settings
```

Debe mostrar:
- ✅ Base de datos: OPERATIVA
- ✅ IA (Python): EN LÍNEA
- ✅ Worker de tareas activo

---

## Paso 13 — Activar HTTPS con certificado SSL (recomendado)

HTTPS encripta el tráfico entre el navegador y el servidor. Es necesario si se va a acceder desde internet.

Instalar Certbot (herramienta gratuita de Let's Encrypt):

```bash
apt install certbot python3-certbot-nginx -y
```

Generar e instalar el certificado SSL automáticamente:

```bash
certbot --nginx -d llamadas.innovationtechnologyperu.com
```

Certbot preguntará un correo electrónico para notificaciones de vencimiento. El certificado se renueva automáticamente cada 90 días.

Después de activar HTTPS, actualizar la URL en el `.env`:

```bash
nano /var/www/grabacion_llamada/backend/.env
```

Cambiar la línea:
```
APP_URL=http://llamadas.innovationtechnologyperu.com
```
Por:
```
APP_URL=https://llamadas.innovationtechnologyperu.com
```

Aplicar el cambio:

```bash
docker exec laravel_app php artisan config:clear
```

---

## Despliegues futuros

Cada vez que haya cambios en el código, ejecutar **desde tu computadora de desarrollo (Windows)**:

```bash
bash scripts/deploy.sh
```

Primero asegurarse de haber subido los cambios a GitHub:

```bash
git add .
git commit -m "descripcion del cambio"
git push
```

Luego correr el deploy:

```bash
bash scripts/deploy.sh
```

El script se conecta automáticamente al servidor por SSH y realiza:
1. Descarga el código actualizado desde GitHub (`git pull`)
2. Reconstruye los contenedores Docker si hubo cambios en el Dockerfile
3. Corre migraciones nuevas si las hay
4. Limpia la caché de Laravel
5. Reinicia el servicio Python-AI

---

## Comandos útiles en el servidor

```bash
# Ver estado de todos los contenedores
docker ps

# Ver logs del backend (últimas 50 líneas)
docker logs laravel_app --tail=50

# Ver logs del worker de colas
docker logs laravel_worker --tail=50

# Ver logs de Nginx interno de Docker
docker logs laravel_nginx --tail=20

# Ver estado del servicio Python-AI
supervisorctl status python-ai

# Ver logs en tiempo real del servicio Python-AI
tail -f /var/log/supervisor/python-ai.out.log
tail -f /var/log/supervisor/python-ai.err.log

# Reiniciar todos los contenedores
cd /var/www/grabacion_llamada/backend && docker compose restart

# Reiniciar solo el servicio Python-AI
supervisorctl restart python-ai

# Correr migraciones manualmente
docker exec laravel_app php artisan migrate --force

# Backup de la base de datos
docker exec laravel_app php artisan system:backup
```

---

## Arquitectura en producción

```
Internet
    │
    ▼
Nginx del servidor (puerto 80 / 443 con HTTPS)
    │  llamadas.innovationtechnologyperu.com
    ▼
Docker — Nginx interno (127.0.0.1:8000)
    │
    ├── laravel_app     (PHP-FPM — Laravel 11)
    ├── laravel_worker  (Cola de jobs de IA)
    └── laravel_mysql   (MySQL 8 — datos en volumen Docker persistente)

    │  host.docker.internal:8001
    ▼
Supervisor — Python-AI (FastAPI + Whisper)
    └── Transcripción y análisis de llamadas
```

---

## Solución de problemas comunes

**El panel no carga o da error 502:**
```bash
docker ps                           # verificar que los 4 contenedores estén corriendo
docker logs laravel_nginx --tail=20 # ver errores de Nginx interno
docker logs laravel_app --tail=30   # ver errores de Laravel
```

**El servicio IA aparece desconectado en /settings:**
```bash
supervisorctl status python-ai                    # ver si está corriendo
supervisorctl restart python-ai                   # reiniciarlo
tail -f /var/log/supervisor/python-ai.err.log     # ver el error exacto
```

**Las llamadas no se analizan (quedan en estado pendiente):**
```bash
docker logs laravel_worker --tail=30  # ver errores del worker
docker restart laravel_worker         # reiniciar el worker
```

**Error al correr migraciones:**
```bash
docker exec laravel_app php artisan migrate:status  # ver estado de migraciones
docker exec laravel_app php artisan migrate --force  # forzar ejecución
```

**Nginx da error al recargar:**
```bash
nginx -t  # verificar la configuración antes de recargar
```
