@echo off
echo ===================================================
echo   Istana Laundry Samarinda - Docker Dev Mode (Vite)
echo ===================================================
echo.
echo [1/2] Membuka web browser ke http://localhost:8000 ...
start http://localhost:8000

echo.
echo [2/2] Menjalankan kontainer Docker (Nginx, PHP, MySQL, Vite)...
docker compose up


