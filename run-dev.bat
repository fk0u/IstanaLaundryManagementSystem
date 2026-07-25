@echo off
echo ===================================================
echo   Istana Laundry Samarinda - Docker Dev Mode (Vite)
echo ===================================================
echo.
echo [1/3] Memastikan kontainer Docker berjalan...
docker compose up -d

echo.
echo [2/3] Membuka web browser ke http://localhost:8000 ...
start http://localhost:8000

echo.
echo [3/3] Menjalankan Vite Dev Server (Hot Reload)...
call npm run dev

