#!/bin/sh
# Entrypoint container OneClick Laravel.
# CONTAINER_ROLE: app (web; menjalankan migrate & cache) | queue | scheduler.
set -e

cd /app
ROLE="${CONTAINER_ROLE:-app}"

echo "[entrypoint] role=$ROLE — menunggu MySQL di ${DB_HOST}:${DB_PORT:-3306}..."
until php -r '
    try {
        new PDO("mysql:host=".getenv("DB_HOST").";port=".(getenv("DB_PORT") ?: 3306).";dbname=".getenv("DB_DATABASE"), getenv("DB_USERNAME"), getenv("DB_PASSWORD"));
    } catch (Throwable $e) {
        exit(1);
    }
' 2>/dev/null; do
    sleep 2
done
echo "[entrypoint] MySQL siap."

if [ "$(id -u)" = "0" ]; then
    chown -R www-data:www-data storage bootstrap/cache public/assets/uploads
fi

if [ "$ROLE" = "app" ]; then
    # Migrate di setiap start supaya migration baru dari git pull langsung terpasang (idempotent).
    php artisan migrate --force
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
    echo "[entrypoint] Migrasi & cache selesai."
fi

exec "$@"
