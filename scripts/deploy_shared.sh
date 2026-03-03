#!/usr/bin/env bash
set -euo pipefail

if [[ ! -f artisan ]]; then
  echo "Error: artisan file not found. Run from project root."
  exit 1
fi

echo "[1/9] Composer install"
composer install --no-dev --optimize-autoloader

echo "[2/9] Install frontend deps"
npm ci

echo "[3/9] Build frontend"
npm run build

echo "[4/9] Generate app key (if missing)"
php artisan key:generate --force || true

echo "[5/9] Migrate DB"
php artisan migrate --force

echo "[6/9] Seed core data"
php artisan db:seed --force

echo "[7/9] Optimize caches"
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "[8/9] Storage link"
php artisan storage:link || true

echo "[9/9] Done"
echo "Deploy completed successfully."
