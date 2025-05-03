# Fisinova Core

Sistema de gestión para Unidades de Medicina Física y Rehabilitación, desarrollado con Laravel, PostgreSQL y Docker.

## 🚀 Requisitos

-   Docker
-   Docker Compose
-   Git
-   Bash (Linux, macOS o WSL en Windows)

## 🛠 Instalación rápida

Clona el repositorio y ejecuta el script de instalación:

```bash
git clone https://github.com/tu-usuario/fisinova-core.git
cd fisinova-core
chmod +x setup.sh
./setup.sh
```

Este script:

1. Copia el archivo .env si no existe.
2. Construye y levanta los contenedores.
3. Instala dependencias con Composer.
4. Ejecuta migraciones de base de datos.
5. Detecta el puerto real para acceso web.

## ⚙️ Servicios incluidos

    Laravel (PHP 8.3)

    PostgreSQL 15

    Nginx

    Composer

## 🧪 Comandos útiles

```bash
# Acceder al contenedor de la app
docker exec -it fisinova-core bash

# Ver logs
docker compose logs -f

# Detener los contenedores
docker compose down
```
