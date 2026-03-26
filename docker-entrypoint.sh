#!/bin/sh
# =============================================================================
# docker-entrypoint.sh — Production entrypoint for Laravel on PHP-FPM
# =============================================================================
# This script is idempotent: safe to re-run on container restarts.
# Vite assets are built at image build time (Dockerfile), NOT here.
# =============================================================================

set -e

echo "==> [1/6] Installing Composer dependencies (production, optimized)..."
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

echo "==> [2/6] Setting file permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache

echo "==> [3/6] Caching configuration..."
php artisan config:cache

echo "==> [4/6] Caching routes..."
# Clear first to avoid stale entries, then cache fresh
php artisan route:clear
php artisan route:cache

echo "==> [5/6] Caching views..."
php artisan view:cache

echo "==> [6/6] Running database migrations (--force for production)..."
php artisan migrate --force

echo "==> [DONE] Running php artisan optimize..."
php artisan optimize

echo "==> Starting PHP-FPM..."
exec "$@"
