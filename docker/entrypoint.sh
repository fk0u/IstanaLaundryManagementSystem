#!/bin/sh

# Exit immediately if a command exits with a non-zero status
set -e

echo "============================================"
echo " Istana Laundry Management System - Docker"
echo "============================================"

echo "[1/5] Menunggu database MySQL siap..."
until nc -z -w5 db 3306 2>/dev/null
do
  echo "      Database belum siap. Menunggu 3 detik..."
  sleep 3
done
echo "      ✓ Database MySQL siap!"

echo "[2/5] Memeriksa APP_KEY..."
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo "      Menghasilkan kunci aplikasi baru..."
    php artisan key:generate --force
fi
echo "      ✓ APP_KEY tersedia."

echo "[3/5] Mengoptimasi cache konfigurasi..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "      ✓ Cache berhasil dioptimasi."

echo "[4/5] Menjalankan migrasi database..."
php artisan migrate --force
echo "      ✓ Migrasi selesai."

echo "[5/5] Menyemai data awal..."
php artisan db:seed --force
echo "      ✓ Data awal berhasil disemai."

echo "============================================"
echo " Aplikasi siap! Memulai PHP-FPM..."
echo "============================================"

exec php-fpm
