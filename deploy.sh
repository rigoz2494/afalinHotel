#!/usr/bin/env bash
#
# Production VPS deploy. Builds and starts the app via Sail's own
# compose.yaml, then runs composer/npm/migrate *inside* the container —
# never on the bare host, since the host has no PHP and `vp build --ssr`
# (via vite-plugin-wayfinder) shells out to `php artisan wayfinder:generate`.
#
# Usage: ./deploy.sh [--seed]
#   --seed  Also run the database seeders. Only pass this on a fresh
#           database — it's additive (updateOrCreate-based), not
#           migrate:fresh, so it never touches existing production data.

set -euo pipefail
cd "$(dirname "$0")"

sail() { ./vendor/bin/sail "$@"; }

sail up -d --build laravel.test pgsql

sail composer install --no-dev --optimize-autoloader

sail npm ci
sail npm run build:ssr

# migrate:fresh is blocked by Laravel itself in production (APP_ENV=production)
# and would drop live data anyway — migrate --force is the only safe choice here.
sail artisan migrate --force

if [ "${1:-}" = "--seed" ]; then
    sail artisan db:seed --force
fi

sail up -d queue ssr
