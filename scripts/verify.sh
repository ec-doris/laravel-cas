#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

cd "$ROOT_DIR"

if [ ! -f vendor/bin/phpunit ]; then
  echo "Composer dependencies are missing. Run 'composer install' first." >&2
  exit 1
fi

if [ ! -d node_modules ]; then
  echo "Installing npm dependencies..."
  if [ -f package-lock.json ]; then
    npm ci
  else
    npm install
  fi
fi

if ! find node_modules/playwright-core/.local-browsers -maxdepth 1 -type d -name 'chromium-*' >/dev/null 2>&1; then
  echo "Installing local Playwright browser..."
  npm run e2e:install
fi

echo "Running PHPUnit..."
vendor/bin/phpunit

echo "Running Playwright end-to-end tests..."
npm run e2e
