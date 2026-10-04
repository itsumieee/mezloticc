#!/usr/bin/env bash
set -euo pipefail

APP_DIR="/var/www/roblox-account-checker"
BRANCH="${1:-main}"

cd "$APP_DIR"
php artisan down --render="errors::503" --retry=60 || true
trap 'php artisan up || true' EXIT

git fetch origin "$BRANCH"
git pull --ff-only origin "$BRANCH"

composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
npm ci
npm run build

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan queue:restart

sudo systemctl reload php8.3-fpm
php artisan up
trap - EXIT

echo "Deploy completed for $BRANCH at $(date -Is)"