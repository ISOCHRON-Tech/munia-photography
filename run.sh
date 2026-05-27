#!/usr/bin/env bash
# run.sh — start all munu-photo dev services
# Usage: bash run.sh
# Stop:  Ctrl+C (kills all child processes automatically)

set -euo pipefail

BOLD='\033[1m'; CYAN='\033[0;36m'; GREEN='\033[0;32m'; RESET='\033[0m'
log() { echo -e "${BOLD}${CYAN}▶  $1${RESET}"; }

# ── Free required ports ───────────────────────────────────────────────────────
log "Freeing ports 8000 and 5173…"
lsof -ti:8000,5173 2>/dev/null | xargs kill -9 2>/dev/null || true
sleep 0.5

# ── Kill any stale artisan serve / queue:work / vite processes ────────────────
pkill -f "artisan serve"      2>/dev/null || true
pkill -f "artisan queue:work" 2>/dev/null || true
pkill -f "vite"               2>/dev/null || true
sleep 0.5

# ── Ensure storage symlink exists ─────────────────────────────────────────────
php artisan storage:link --force 2>/dev/null || true

# ── Trap so Ctrl+C kills every child ─────────────────────────────────────────
trap 'echo -e "\n${BOLD}Shutting down…${RESET}"; kill 0' INT TERM

# ── Launch services ───────────────────────────────────────────────────────────
log "Starting PHP backend  → http://127.0.0.1:8000"
php artisan serve --port=8000 &

log "Starting Vite HMR     → http://127.0.0.1:5173"
npm run dev &

log "Starting queue worker (image processing)"
php artisan queue:work --queue=default --tries=3 --sleep=3 &

echo -e "\n${GREEN}${BOLD}All services running. Press Ctrl+C to stop.${RESET}\n"

wait
