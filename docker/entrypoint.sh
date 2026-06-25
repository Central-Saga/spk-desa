#!/bin/sh
set -e

cd /var/www/html

echo "[entrypoint] Waiting for MySQL at ${DB_HOST}:${DB_PORT}..."
ATTEMPTS=0
until php -r "new PDO('mysql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));" >/dev/null 2>&1; do
    ATTEMPTS=$((ATTEMPTS + 1))
    if [ "$ATTEMPTS" -ge 60 ]; then
        echo "[entrypoint] MySQL still unreachable after 60 attempts. Aborting."
        exit 1
    fi
    sleep 2
done
echo "[entrypoint] MySQL is reachable."

mkdir -p \
    storage/app/public \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache \
    storage/logs \
    bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

php artisan storage:link --force >/dev/null 2>&1 || true

echo "[entrypoint] Running migrations..."
php artisan migrate --force --no-interaction

echo "[entrypoint] Caching config / routes / views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache >/dev/null 2>&1 || true

echo "[entrypoint] Bootstrap complete. Handing off to supervisord."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
