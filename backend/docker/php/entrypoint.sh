#!/bin/bash
set -e

# Ensure bootstrap/cache and storage have right permissions inside container
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/cache
mkdir -p storage/logs
mkdir -p bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache || true
chmod -R 777 storage bootstrap/cache || true

# Only the main app container should run install and migrations
if [ "$1" = "php-fpm" ]; then
    if [ ! -f "vendor/autoload.php" ]; then
        echo "Installing composer dependencies..."
        composer install --no-interaction --prefer-dist --optimize-autoloader
    fi

    if [ ! -f ".env" ] && [ -f ".env.example" ]; then
        cp .env.example .env
    fi

    if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
        echo "Generating app key..."
        php artisan key:generate --force
    fi

    echo "Running migrations..."
    php artisan migrate --force

else
    echo "Waiting for app dependencies..."
    while [ ! -f "vendor/autoload.php" ]; do
        sleep 2
    done
fi

exec "$@"