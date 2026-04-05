#!/bin/sh
# =============================================================================
# docker-entrypoint.sh — Production entrypoint for Laravel on PHP-FPM
# =============================================================================
# This script is idempotent: safe to re-run on container restarts.
# Vite assets are built at image build time (Dockerfile), NOT here.
# =============================================================================

set -e

if [ ! -f vendor/autoload.php ]; then
    echo "==> vendor/ not found in runtime mount, installing Composer dependencies..."
    composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
else
    echo "==> vendor/ already present, skipping Composer install."
fi

echo "==> [1/4] Setting file permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache

if [ "${APP_ENV}" = "local" ]; then
    echo "==> [2/4] Local environment detected, clearing caches instead of warming production caches..."
    php artisan optimize:clear
else
    echo "==> [2/4] Caching configuration..."
    php artisan config:cache

    echo "==> [3/4] Caching routes..."
    # Clear first to avoid stale entries, then cache fresh
    php artisan route:clear
    php artisan route:cache

    echo "==> [4/4] Caching views..."
    php artisan view:cache
fi

echo "==> Starting PHP-FPM..."
exec "$@"
