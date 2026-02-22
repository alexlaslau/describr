#!/bin/bash
set -e

# Run migrations
php artisan migrate --force

# Cache config and routes for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start supervisor (PHP-FPM + Nginx + Queue Worker)
exec /usr/bin/supervisord -n
