@echo off
echo ===================================================
echo   Istana Laundry Samarinda - Startup Script (MVP)
echo ===================================================
echo.
echo [1/3] Membangun aset frontend (Vite)...
call npm run build

echo.
echo [2/3] Membuka web browser ke http://127.0.0.1:8000 ...
start http://127.0.0.1:8000

echo.
echo [3/3] Menjalankan Laravel PHP server...
php artisan serve
