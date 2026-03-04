#!/usr/bin/env sh
set -eu

cd /var/www/html

if [ -z "${APP_KEY:-}" ]; then
    echo "APP_KEY is not set. Configure APP_KEY in Railway variables."
    exit 1
fi

php artisan optimize:clear || true
php artisan storage:link || true
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec php -d variables_order=EGPCS artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
