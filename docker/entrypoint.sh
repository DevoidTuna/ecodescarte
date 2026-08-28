#!/bin/sh
# Prepares the application on every container boot: .env, key, migrations, seed.
# It runs at start rather than at build time because none of these steps are
# possible during `docker build` — there is no .env and no database yet.
set -e

cd /app

# .env is gitignored, so on the first boot the container creates its own.
if [ ! -f .env ]; then
    echo "==> creating .env from .env.example"
    cp .env.example .env
fi

# Writes a key into .env, creating the line if it is not there yet.
set_env() {
    if grep -q "^$1=" .env; then
        sed -i "s|^$1=.*|$1=$2|" .env
    else
        printf '%s=%s\n' "$1" "$2" >> .env
    fi
}

# The database credentials have to live in .env, not only in the process
# environment: `artisan serve` forwards just a fixed allowlist of variables to
# the server it spawns (APP_ENV, PATH, LARAVEL_SAIL and the Xdebug ones).
# DB_HOST and the rest are dropped, so the served app would read .env and try
# 127.0.0.1 — even with the migrations having worked, since those run in the
# parent process.
echo "==> applying the compose credentials to .env"
set_env DB_CONNECTION "${DB_CONNECTION:-pgsql}"
set_env DB_HOST "${DB_HOST:-db}"
set_env DB_PORT "${DB_PORT:-5432}"
set_env DB_DATABASE "${DB_DATABASE:-ecodescarte}"
set_env DB_USERNAME "${DB_USERNAME:-ecodescarte}"
set_env DB_PASSWORD "${DB_PASSWORD:-secret}"

# Generates the APP_KEY only when there is not one already.
if ! grep -q '^APP_KEY=base64:' .env; then
    echo "==> generating APP_KEY"
    php artisan key:generate --force
fi

# The compose healthcheck already waits for PostgreSQL; this wait covers the
# case of the container being started outside compose.
until pg_isready -h "${DB_HOST:-db}" -p "${DB_PORT:-5432}" -U "${DB_USERNAME:-ecodescarte}" >/dev/null 2>&1; do
    echo "==> waiting for PostgreSQL at ${DB_HOST:-db}:${DB_PORT:-5432}"
    sleep 2
done

echo "==> migrations"
php artisan migrate --force

# The seeder uses firstOrCreate, so repeating it on every boot is safe.
echo "==> seed"
php artisan db:seed --force

exec "$@"
