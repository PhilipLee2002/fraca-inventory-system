@echo off
echo.
echo  Fraca Servcom Inventory
echo  Opening http://127.0.0.1:8000/login
echo.
echo  First start the app if it is not already running:
echo    php artisan serve
echo    npm run dev
echo.
start "" "http://127.0.0.1:8000/login"
