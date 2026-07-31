@echo off
setlocal
set "PATH=C:\laragon\bin\php\php-8.4.6-nts-Win32-vs17-x64;C:\laragon\bin\composer;%PATH%"

echo ===================================================
echo   Istana Laundry Samarinda - Local Serve Mode
echo ===================================================
echo.
echo [1/2] Membuka web browser ke http://127.0.0.1:8000 ...
timeout /t 2 /nobreak >nul
start http://127.0.0.1:8000

echo.
echo [2/2] Menjalankan PHP Artisan Serve...
php artisan serve --port=8000
