#!/bin/sh
set -e

echo "🔔 Booting NHMP 130 CRM container..."

cd /var/www/html

# Ensure essential directories exist
mkdir -p storage/framework/sessions \
         storage/framework/views \
         storage/framework/cache/data \
         storage/logs \
         bootstrap/cache

# Clear anything cached wrongly during build transport
# Create an empty packages.php to satisfy Laravel if needed (though usually not necessary)
# ── Database migrations ─────────────────────────────────────────────────────
# IMPORTANT: Migrations are NOT run automatically on every boot.
# Run manually during deployment with:
#   docker exec crm_app php artisan migrate --force
#
# To check pending migrations without applying:
#   docker exec crm_app php artisan migrate:status
#
# For CI/CD pipelines, inject RUN_MIGRATIONS=true as an env var to auto-run:
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    echo "🔔 Running DB Migrations (RUN_MIGRATIONS=true)..."
    php artisan migrate --force
else
    echo "ℹ️  Skipping migrations (set RUN_MIGRATIONS=true to run on boot)"
fi

# Clear caches now that DB is ready
php artisan optimize:clear || echo "⚠️  Could not clear caches (DB might not be ready)"

echo "🔔 Caching Application State for JIT & Highest Performance..."
php artisan config:cache || echo "⚠️  Failed to cache config"
php artisan event:cache || echo "⚠️  Failed to cache events"
php artisan route:cache || echo "⚠️  Failed to cache routes"
php artisan view:cache || echo "⚠️  Failed to cache views"

echo "🔔 System is highly optimized. Starting PHP-FPM daemon..."
# Fire up supervisor or php-fpm
exec "$@"
