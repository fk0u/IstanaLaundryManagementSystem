#!/bin/sh

# Exit immediately if a command exits with a non-zero status
set -e

echo "Menunggu database MySQL siap..."
until nc -z -v -w30 db 3306
do
  echo "Database belum siap. Menunggu 3 detik..."
  sleep 3
done
echo "Database MySQL siap!"

# Generate key if not exists
if [ -z "$APP_KEY" ]; then
    echo "Menghasilkan kunci aplikasi (APP_KEY)..."
    php artisan key:generate --force
fi

# Run migrations
echo "Menjalankan migrasi database..."
php artisan migrate --force

# Seed database
echo "Menyemai data ERP..."
php artisan db:seed --force

# Start php-fpm
echo "Memulai PHP-FPM..."
exec php-fpm
