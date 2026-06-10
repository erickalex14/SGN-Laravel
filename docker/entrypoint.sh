#!/usr/bin/env sh
set -e

cd /var/www/html

if [ ! -f .env ]; then
  echo "ERROR: falta el archivo /var/www/html/.env" >&2
  exit 1
fi

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache
chown -R nobody:nobody storage bootstrap/cache || true
chmod -R ug+rw storage bootstrap/cache || true
rm -f bootstrap/cache/*.php || true

if [ "${RUN_STORAGE_LINK:-true}" = "true" ] && [ ! -L public/storage ]; then
  php artisan storage:link || true
fi

php artisan optimize:clear || true

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
  php artisan migrate --force
fi

if [ "${CACHE_LARAVEL:-true}" = "true" ]; then
  php artisan config:cache || true
  php artisan route:cache || true
  php artisan view:cache || true
fi

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8001}"
