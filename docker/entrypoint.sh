#!/usr/bin/env sh
set -e

cd /var/www/html

if [ ! -f .env ] && [ -f .env.example ]; then
  cp .env.example .env
fi

if [ "${GENERATE_APP_KEY:-true}" = "true" ]; then
  php artisan key:generate --force || true
fi

php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

PORT_TO_USE="${PORT:-10000}"
exec php artisan serve --host=0.0.0.0 --port="${PORT_TO_USE}"
