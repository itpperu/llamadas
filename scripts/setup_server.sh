#!/bin/bash
# =============================================================================
# setup_server.sh — Instalación inicial del servidor (ejecutar UNA sola vez)
# Servidor: Ubuntu | Usuario: root
# Uso: bash setup_server.sh
# =============================================================================
set -e

REPO_URL="https://github.com/itpperu/llamadas.git"
APP_DIR="/var/www/grabacion_llamada"
DOMAIN="llamadas.innovationtechnologyperu.com"
PYTHON_AI_DIR="$APP_DIR/services/python-ai"
DB_PASSWORD="$(openssl rand -base64 20 | tr -d '=+/' | cut -c1-20)"

echo ""
echo "╔══════════════════════════════════════════════════════╗"
echo "║     AI Call Sync — Instalación inicial del servidor  ║"
echo "╚══════════════════════════════════════════════════════╝"
echo ""

# ── 1. Actualizar sistema ─────────────────────────────────────────────────────
echo "[1/9] Actualizando sistema..."
apt-get update -qq && apt-get upgrade -y -qq

# ── 2. Instalar Docker ────────────────────────────────────────────────────────
echo "[2/9] Instalando Docker..."
if ! command -v docker &>/dev/null; then
    curl -fsSL https://get.docker.com | sh
    systemctl enable docker
    systemctl start docker
    echo "      Docker instalado correctamente."
else
    echo "      Docker ya está instalado: $(docker --version)"
fi

# ── 3. Instalar dependencias del sistema ──────────────────────────────────────
echo "[3/9] Instalando dependencias del sistema..."
apt-get install -y -qq supervisor python3-venv python3-pip git curl openssl

# ── 4. Clonar repositorio ─────────────────────────────────────────────────────
echo "[4/9] Clonando repositorio..."
if [ -d "$APP_DIR/.git" ]; then
    echo "      Repositorio ya existe, haciendo pull..."
    cd "$APP_DIR" && git pull origin main
else
    git clone "$REPO_URL" "$APP_DIR"
fi
echo "      Código en: $APP_DIR"

# ── 5. Configurar .env del backend ────────────────────────────────────────────
echo "[5/9] Configurando entorno del backend..."
cd "$APP_DIR/backend"

if [ ! -f ".env" ]; then
    cp .env.example .env

    # Ajustes automáticos de producción
    sed -i "s|APP_ENV=.*|APP_ENV=production|" .env
    sed -i "s|APP_DEBUG=.*|APP_DEBUG=false|" .env
    sed -i "s|APP_URL=.*|APP_URL=http://$DOMAIN|" .env
    sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=$DB_PASSWORD|" .env
    sed -i "s|MYSQL_PASSWORD=.*|MYSQL_PASSWORD=$DB_PASSWORD|" docker-compose.yml 2>/dev/null || true

    echo ""
    echo "  ┌─────────────────────────────────────────────────────┐"
    echo "  │  GUARDA ESTA CONTRASEÑA DE BASE DE DATOS:           │"
    echo "  │  DB_PASSWORD=$DB_PASSWORD"
    echo "  └─────────────────────────────────────────────────────┘"
    echo ""
else
    echo "      .env ya existe, no se sobreescribe."
fi

# ── 6. Levantar Docker y correr migraciones ───────────────────────────────────
echo "[6/9] Levantando contenedores Docker..."
cd "$APP_DIR/backend"
docker compose up -d --build

echo "      Esperando que la base de datos esté lista..."
sleep 20

echo "      Generando APP_KEY..."
docker exec laravel_app php artisan key:generate --force

echo "      Corriendo migraciones..."
docker exec laravel_app php artisan migrate --force

echo "      Limpiando caché..."
docker exec laravel_app php artisan config:clear
docker exec laravel_app php artisan route:clear
docker exec laravel_app php artisan view:clear

# ── 7. Configurar servicio Python-AI ─────────────────────────────────────────
echo "[7/9] Configurando servicio Python-AI..."
cd "$PYTHON_AI_DIR"

if [ ! -d "venv" ]; then
    python3 -m venv venv
fi

source venv/bin/activate
pip install -q -r requirements.txt
deactivate

# Configuración de Supervisor para Python-AI
cat > /etc/supervisor/conf.d/python-ai.conf << EOF
[program:python-ai]
command=$PYTHON_AI_DIR/venv/bin/uvicorn app.main:app --host 0.0.0.0 --port 8001 --workers 1
directory=$PYTHON_AI_DIR
user=root
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
stderr_logfile=/var/log/supervisor/python-ai.err.log
stdout_logfile=/var/log/supervisor/python-ai.out.log
environment=PYTHONUNBUFFERED="1"
EOF

supervisorctl reread
supervisorctl update
supervisorctl start python-ai || supervisorctl restart python-ai
echo "      Python-AI corriendo en puerto 8001."

# ── 8. Configurar Nginx para el subdominio ────────────────────────────────────
echo "[8/9] Configurando Nginx para $DOMAIN..."
cat > /etc/nginx/sites-available/llamadas.conf << EOF
server {
    listen 80;
    server_name $DOMAIN;

    client_max_body_size 50M;

    # Proxy hacia Docker (Laravel + Nginx interno)
    location / {
        proxy_pass         http://127.0.0.1:8000;
        proxy_set_header   Host \$host;
        proxy_set_header   X-Real-IP \$remote_addr;
        proxy_set_header   X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header   X-Forwarded-Proto \$scheme;
        proxy_read_timeout 300s;
        proxy_send_timeout 300s;
        proxy_connect_timeout 60s;
    }
}
EOF

ln -sf /etc/nginx/sites-available/llamadas.conf /etc/nginx/sites-enabled/llamadas.conf
nginx -t && systemctl reload nginx
echo "      Nginx configurado para $DOMAIN."

# ── 9. Resumen final ──────────────────────────────────────────────────────────
echo ""
echo "╔══════════════════════════════════════════════════════╗"
echo "║                Instalación completada                ║"
echo "╠══════════════════════════════════════════════════════╣"
echo "║  URL:       http://$DOMAIN"
echo "║  Backend:   Docker en 127.0.0.1:8000"
echo "║  Python-AI: Supervisor en 0.0.0.0:8001"
echo "╠══════════════════════════════════════════════════════╣"
echo "║  Próximo paso recomendado: configurar HTTPS          ║"
echo "║  apt install certbot python3-certbot-nginx           ║"
echo "║  certbot --nginx -d $DOMAIN"
echo "╚══════════════════════════════════════════════════════╝"
echo ""
echo "  Para crear el usuario admin del panel:"
echo "  docker exec laravel_app php artisan tinker"
echo "  >>> App\\Models\\User::create(['name'=>'Administrador','email'=>'admin@tudominio.com','password'=>bcrypt('TuClave2026!'),'rol'=>'admin']);"
echo ""
