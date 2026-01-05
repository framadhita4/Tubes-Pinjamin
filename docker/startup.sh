#!/bin/sh

# Exit on error
set -e

# Run Migrations
echo "Running migrations..."
php artisan migrate --force

# Cache config/routes
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start Supervisor (which starts Nginx & PHP)
echo "Starting Supervisor..."
/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf