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

# Migrations — tolerate "table already exists" when DB volume is pre-populated
php artisan migrate --force 2>&1 | tee /tmp/migrate.out; \
MIGRATE_EXIT=${PIPESTATUS[0]}; \
if [[ $MIGRATE_EXIT -ne 0 ]]; then \
    grep -q "already exists\|already been" /tmp/migrate.out \
        && echo "⚠  Some tables already existed — treating as migrated" \
        || { cat /tmp/migrate.out; exit 1; }; \
fi

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
