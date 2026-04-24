#!/bin/bash
# =============================================================================
# deploy.sh — Despliegue a producción desde tu máquina local
# Uso: bash scripts/deploy.sh
# =============================================================================

SERVER="root@161.97.71.74"
APP_DIR="/var/www/grabacion_llamada"
BRANCH="main"

echo ""
echo "┌─────────────────────────────────────────────┐"
echo "│  Desplegando AI Call Sync en producción...  │"
echo "└─────────────────────────────────────────────┘"
echo ""

ssh -o StrictHostKeyChecking=no "$SERVER" bash << EOF
set -e
cd $APP_DIR

echo "[1/5] Actualizando código..."
git pull origin $BRANCH

echo "[2/5] Reconstruyendo contenedores si hubo cambios en Dockerfile..."
cd backend
docker compose up -d --build

echo "[3/5] Corriendo migraciones..."
docker exec laravel_app php artisan migrate --force

echo "[4/5] Limpiando caché..."
docker exec laravel_app php artisan config:clear
docker exec laravel_app php artisan route:clear
docker exec laravel_app php artisan view:clear

echo "[5/5] Reiniciando servicio Python-AI..."
supervisorctl restart python-ai

echo ""
echo "✓ Deploy completado → http://llamadas.innovationtechnologyperu.com"
EOF
