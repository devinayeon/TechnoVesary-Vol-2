#!/bin/sh
set -e

cd /var/www/html

# Direktori runtime harus ada karena tidak dikirim lewat image.
mkdir -p \
    storage/app/private \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

# Public storage symlink (idempoten).
php artisan storage:link --force >/dev/null 2>&1 || true

# Cache konfigurasi/route/view hanya di produksi.
if [ "${APP_ENV:-production}" = "production" ]; then
    php artisan optimize
fi

exec "$@"
