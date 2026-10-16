#!/usr/bin/env bash
#
# Production VPS deploy. Talks to `docker compose` directly rather than
# through ./vendor/bin/sail (that script is itself a composer dependency,
# so it can't exist yet on a fresh clone with no vendor/ installed).
# compose.yaml builds from this project's own Dockerfile, so unlike Sail's
# bundled Ubuntu+Sury-PPA runtime image, nothing here needs vendor/ to
# exist before the image can even build. WWWUSER/WWWGROUP mirror exactly
# what the sail script itself exports, so files created in the
# bind-mounted volume keep sane host ownership.
#
# Everything composer/npm/artisan runs *inside* the container — never on
# the bare host, since the host has no PHP and `vp build --ssr` (via
# vite-plugin-wayfinder) shells out to `php artisan wayfinder:generate`.
#
# Usage: ./deploy.sh [--seed]
#   --seed  Also run the database seeders. Only pass this on a fresh
#           database — it's additive (updateOrCreate-based), not
#           migrate:fresh, so it never touches existing production data.

set -euo pipefail
cd "$(dirname "$0")"

export WWWUSER=${WWWUSER:-$UID}
export WWWGROUP=${WWWGROUP:-$(id -g)}

dc() { docker compose "$@"; }
run() { dc exec -T laravel.test "$@"; }

dc up -d --build laravel.test pgsql

run composer install --no-dev --optimize-autoloader

run npm ci
run npm run build:ssr

# migrate:fresh is blocked by Laravel itself in production (APP_ENV=production)
# and would drop live data anyway — migrate --force is the only safe choice here.
run php artisan migrate --force

if [ "${1:-}" = "--seed" ]; then
    run php artisan db:seed --force
fi

dc up -d queue ssr
