#!/usr/bin/env bash
set -e

echo "▶ Waiting for MySQL…"
until php -r "new PDO('mysql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT').';dbname='.getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));" 2>/dev/null; do
    sleep 2
done
echo "✓ MySQL ready"

# Generate app key if not set
if [[ -z "${APP_KEY}" ]]; then
    php artisan key:generate --force
fi

# Migrations
php artisan migrate --force

# Storage symlink
php artisan storage:link --force 2>/dev/null || true

# Cache everything
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "✓ Bootstrap complete — starting Apache"
exec apache2-foreground
