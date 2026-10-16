#!/usr/bin/env bash
#
# Production VPS deploy. Talks to `docker compose` directly rather than
# through ./vendor/bin/sail, since on a fresh clone vendor/ (and so the
# sail script itself) doesn't exist yet — it's what `composer install`
# below is for. WWWUSER/WWWGROUP below mirror exactly what the sail
# script itself exports, so files created in the bind-mounted volume
# keep sane host ownership.
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

# compose.yaml's build context is vendor/laravel/sail/runtimes/8.4, which
# doesn't exist on a fresh clone until composer has run at least once —
# and the host has no PHP/Composer to run it directly. Bootstrap with
# Laravel's own documented throwaway-container pattern (see "Installing
# Composer Dependencies For Existing Projects" in the Sail docs); the
# bind mount means vendor/ lands on the host too, not just in this
# one-shot container, so `dc up --build` right after can find it.
# No --no-dev here: laravel/sail lives in require-dev, and its
# runtimes/8.4/Dockerfile is exactly what this bootstrap needs — the
# `composer install --no-dev` right after `dc up` strips dev packages
# back out for the actual running app, same as always.
# --ignore-platform-reqs is only safe here because this container is
# never anything but a one-shot installer — the real app container
# built below genuinely has ext-intl, pdo_pgsql, etc.
if [ ! -f vendor/laravel/sail/runtimes/8.4/Dockerfile ]; then
    docker run --rm \
        -u "$(id -u):$(id -g)" \
        -v "$(pwd):/var/www/html" \
        -w /var/www/html \
        laravelsail/php84-composer:latest \
        composer install --optimize-autoloader --ignore-platform-reqs
fi

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
