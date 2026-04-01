#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
APP_URL="${APP_URL:-http://127.0.0.1:8001}"
CAS_URL="${CAS_URL:-http://127.0.0.1:9800/cas}"
DB_DATABASE="${DB_DATABASE:-$ROOT_DIR/vendor/orchestra/testbench-core/laravel/database/database.sqlite}"

mkdir -p "$(dirname "$DB_DATABASE")"
touch "$DB_DATABASE"

export APP_ENV="${APP_ENV:-local}"
export APP_KEY="${APP_KEY:-base64:5jyd1tK2K5F+3L8Lw2XrE/8gYmQDXHsk1sCO2A9M3Bc=}"
export APP_URL
export CAS_URL
export CAS_REDIRECT_LOGIN_ROUTE="${CAS_REDIRECT_LOGIN_ROUTE:-dashboard}"
export CAS_REDIRECT_LOGOUT_URL="${CAS_REDIRECT_LOGOUT_URL:-$APP_URL/}"
export DB_CONNECTION="${DB_CONNECTION:-sqlite}"
export DB_DATABASE
export CACHE_DRIVER="${CACHE_DRIVER:-array}"
export QUEUE_CONNECTION="${QUEUE_CONNECTION:-sync}"
export SESSION_DRIVER="${SESSION_DRIVER:-file}"

php "$ROOT_DIR/vendor/bin/testbench" workbench:build --ansi
exec php "$ROOT_DIR/vendor/bin/testbench" serve --host=127.0.0.1 --port=8001 --no-reload --ansi
