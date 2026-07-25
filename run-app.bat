@echo off
echo ===================================================
echo   Istana Laundry Samarinda - Docker Production Mode
echo ===================================================
echo.
echo [1/2] Membuka web browser ke http://localhost:8000 ...
start http://localhost:8000

echo.
echo [2/2] Menjalankan kontainer Docker (Nginx, PHP, MySQL)...
docker compose up

