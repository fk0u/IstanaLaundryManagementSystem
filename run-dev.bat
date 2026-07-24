@echo off
echo ===================================================
echo   Istana Laundry Samarinda - Dev mode (Hot Reload)
echo ===================================================
echo.
echo [1/2] Membuka web browser ke http://127.0.0.1:8000 ...
start http://127.0.0.1:8000

echo.
echo [2/2] Menjalankan server Laravel & Vite (Dev)...
call npm run start
