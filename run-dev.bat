@echo off
setlocal
set "PATH=C:\laragon\bin\php\php-8.4.6-nts-Win32-vs17-x64;C:\laragon\bin\composer;%PATH%"

echo ===================================================
echo   Istana Laundry Samarinda - Local Dev Mode
echo ===================================================
echo.
echo [1/3] Membuka web browser ke http://127.0.0.1:8000 ...
timeout /t 2 /nobreak >nul
start http://127.0.0.1:8000

echo.
echo [2/3] Menjalankan Vite Dev Server di jendela baru...
start "Vite Dev Server" cmd /k "npm run dev"

echo.
echo [3/3] Menjalankan PHP Artisan Serve...
php artisan serve --port=8000
