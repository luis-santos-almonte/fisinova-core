#!/bin/bash

set -e

echo "🚀 Iniciando configuración de la app..."

# Copiar .env si no existe
if [ ! -f .env ]; then
    echo "📄 Copiando archivo .env..."
    cp .env.example .env
fi

# Construir y levantar contenedores
echo "🐳 Levantando contenedores con Docker..."
docker compose up -d --build

# Esperar unos segundos
echo "⏳ Esperando a que los contenedores estén listos..."
sleep 10

# Instalar dependencias
echo "📦 Instalando dependencias y preparando Laravel..."
docker exec -it fisinova-core composer install
docker exec -it fisinova-core php artisan key:generate
docker exec -it fisinova-core php artisan migrate

# Obtener puerto real mapeado al 80 del contenedor nginx
PORT=$(docker inspect --format='{{(index (index .NetworkSettings.Ports "80/tcp") 0).HostPort}}' nginx-web)

echo "✅ ¡Todo listo! Puedes acceder a la app en: http://localhost:$PORT"
