#!/bin/sh
set -e

cd /app

echo "Running composer install..."
composer install --no-interaction --prefer-dist

echo "Starting PHP server on 0.0.0.0:8000..."
exec php -d display_errors=stderr -d log_errors=1 -S 0.0.0.0:8000 -t public
