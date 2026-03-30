#!/usr/bin/env sh
set -e

mkdir -p /var/www/html/storage/framework/views \
    /var/www/html/storage/framework/cache \
    /var/www/html/storage/framework/cache/data \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/testing \
    /var/www/html/storage/logs \
    /var/www/html/bootstrap/cache

touch /var/www/html/storage/logs/laravel.log

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true
php artisan migrate --force || true

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

apache2-foreground