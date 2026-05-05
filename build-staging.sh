#!/usr/bin/env bash
# exit on error
set -o errexit

composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Build assets (Vite)
npm install
npm run build

# Run migrations for staging
php artisan migrate --force
