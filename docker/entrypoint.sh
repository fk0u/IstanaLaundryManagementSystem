#!/bin/sh

# Exit immediately if a command exits with a non-zero status
set -e

echo "============================================"
echo " Istana Laundry Management System - Docker"
echo "============================================"

echo "[1/6] Menunggu database MySQL siap..."
until nc -z -w5 db 3306 2>/dev/null
do
  echo "      Database belum siap. Menunggu 3 detik..."
  sleep 3
done
echo "      ✓ Database MySQL siap!"

echo "[2/6] Membersihkan cache bootstrap lama..."
rm -f /var/www/bootstrap/cache/packages.php
rm -f /var/www/bootstrap/cache/services.php
rm -f /var/www/bootstrap/cache/config.php
rm -f /var/www/bootstrap/cache/routes-v7.php
rm -f /var/www/bootstrap/cache/events.php
echo "      ✓ Cache bootstrap dibersihkan."

echo "[3/6] Memeriksa APP_KEY..."
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo "      Menghasilkan kunci aplikasi baru..."
    php artisan key:generate --force
fi
echo "      ✓ APP_KEY tersedia."

echo "[4/6] Mengoptimasi cache konfigurasi..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "      ✓ Cache berhasil dioptimasi."

echo "[5/6] Menjalankan migrasi database..."
php artisan migrate --force
echo "      ✓ Migrasi selesai."

echo "[6/6] Menyemai data awal..."
# Only seed in non-production environments to prevent data overwrites
if [ "$APP_ENV" != "production" ]; then
    php artisan db:seed --force
    echo "      ✓ Data awal berhasil disemai."
else
    echo "      ⚠ Skipping db:seed in production environment."
fi

echo "============================================"
echo " Aplikasi siap! Memulai PHP-FPM..."
echo "============================================"

exec php-fpm
