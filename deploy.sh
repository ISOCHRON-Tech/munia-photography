#!/usr/bin/env bash
# =============================================================================
# munu-photo — cPanel Deployment Script
# Run this from the project root via SSH on your cPanel server.
# Prerequisites: PHP 8.4+, Composer, Node.js, npm on $PATH.
# =============================================================================

set -euo pipefail

BOLD='\033[1m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RESET='\033[0m'

step() { echo -e "\n${BOLD}${YELLOW}▶  $1${RESET}"; }
ok()   { echo -e "${GREEN}✓  $1${RESET}"; }

# ── 1. Environment file ───────────────────────────────────────────────────────
step "1/10  Checking .env"
if [[ ! -f .env ]]; then
    cp .env.example .env
    echo "    ⚠  .env created from example — fill in DB credentials before migrating."
fi
ok ".env ready"

# ── 2. Composer dependencies (production only) ────────────────────────────────
step "2/10  Installing PHP dependencies"
composer install --no-dev --optimize-autoloader --no-interaction
ok "Composer installed"

# ── 3. Generate app key (idempotent) ─────────────────────────────────────────
step "3/10  Ensuring APP_KEY"
php artisan key:generate --no-interaction --force
ok "Key set"

# ── 4. Frontend assets ────────────────────────────────────────────────────────
step "4/10  Building frontend assets"
npm ci --omit=dev
npm run build
ok "Assets compiled to public/build/"

# ── 5. Database migrations ────────────────────────────────────────────────────
step "5/10  Running migrations"
php artisan migrate --force
ok "Migrations done"

# ── 6. Cache all the things ───────────────────────────────────────────────────
step "6/10  Caching config / routes / views"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
ok "All caches warmed"

# ── 7. Storage symlink ────────────────────────────────────────────────────────
step "7/10  Storage symlink"
php artisan storage:link --force
ok "public/storage → storage/app/public"

# ── 8. Queue table (if not already created) ──────────────────────────────────
step "8/10  Ensuring queue tables"
php artisan queue:table 2>/dev/null || true
php artisan migrate --force
ok "Queue tables ready"

# ── 9. File permissions ───────────────────────────────────────────────────────
step "9/10  Setting permissions"
find storage bootstrap/cache -type d -exec chmod 775 {} \;
find storage bootstrap/cache -type f -exec chmod 664 {} \;
ok "Permissions set"

# ── 10. Cron hint ────────────────────────────────────────────────────────────
step "10/10  cPanel Cron Jobs to add"
cat <<'CRON'

  ┌─────────────────────────────────────────────────────────────────────────┐
  │  Add these two cron jobs in cPanel → Cron Jobs:                         │
  │                                                                         │
  │  Every minute — Laravel scheduler:                                      │
  │    * * * * * /usr/local/bin/php /home/USER/PROJECT_PATH/artisan         │
  │              schedule:run >> /dev/null 2>&1                             │
  │                                                                         │
  │  Every 5 minutes — Queue worker (database driver):                      │
  │    */5 * * * * /usr/local/bin/php /home/USER/PROJECT_PATH/artisan       │
  │              queue:work --queue=default --max-jobs=50 --stop-when-empty │
  │              >> /dev/null 2>&1                                          │
  └─────────────────────────────────────────────────────────────────────────┘

CRON

echo -e "\n${BOLD}${GREEN}🎉  Deployment complete!${RESET}"
echo "    Point your cPanel document root to: $(pwd)/public"
