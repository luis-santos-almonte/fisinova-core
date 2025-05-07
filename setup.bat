@echo off
chcp 65001 >nul
setlocal enabledelayedexpansion

echo 🚀 Iniciando configuración de la app...

:: Verificar si Docker está activo
docker info >nul 2>&1
if errorlevel 1 (
    echo ❌ Docker no está iniciado. Por favor, abre Docker Desktop y espera a que esté en ejecución.
    pause
    exit /b
)

:: Verificar si .env existe
if not exist ".env" (
    echo 📄 Copiando archivo .env...
    copy .env.example .env >nul
)

:: Construir y levantar contenedores
echo 🐳 Levantando contenedores con Docker...
docker compose up -d --build

:: Esperar unos segundos
echo ⏳ Esperando a que los contenedores estén listos...
timeout /t 10 /nobreak >nul

:: Instalar dependencias y preparar Laravel
echo 📦 Instalando dependencias y preparando Laravel...
docker exec -it fisinova-core composer install
docker exec -it fisinova-core php artisan key:generate
docker exec -it fisinova-core php artisan migrate

:: Obtener el puerto mapeado del contenedor nginx-web
for /f "tokens=* delims=" %%p in ('docker inspect --format="{{(index (index .NetworkSettings.Ports \"80/tcp\") 0).HostPort}}" nginx-web 2^>nul') do (
    set PORT=%%p
)

if defined PORT (
    echo ✅ ¡Todo listo! Puedes acceder a la app en: http://localhost:!PORT!
) else (
    echo ⚠️ No se pudo obtener el puerto de nginx-web. Asegúrate de que el contenedor esté corriendo.
)

endlocal
pause
