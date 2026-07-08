#!/bin/bash
# Make sure this file has executable permissions, run `chmod +x railway/init-app.sh`

echo "=== Starting init-app.sh ==="
echo "APP_ENV: $APP_ENV"
echo "DB_HOST: $DB_HOST"
echo "DB_DATABASE: $DB_DATABASE"
echo "DB_USERNAME: $DB_USERNAME"

echo "=== Running migrate ==="
php artisan migrate --force
MIGRATE_EXIT=$?
echo "=== migrate exit code: $MIGRATE_EXIT ==="

if [ $MIGRATE_EXIT -ne 0 ]; then
    echo "=== MIGRATE FAILED, skipping seed ==="
else
    echo "=== Running seed ==="
    php artisan db:seed --class=ProductionSeeder --force
fi

echo "=== Clearing cache ==="
php artisan optimize:clear

echo "=== Caching config/routes/views ==="
php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache

echo "=== init-app.sh done ==="
