# Fisinova Core API

Este es un sistema de backend en Laravel para gestionar citas, procedimientos, autorizaciones, pagos y más, diseñado inicialmente para una unidad de Medicina Física y Rehabilitación.

## 🚀 Requisitos

- [Docker Desktop](https://www.docker.com/products/docker-desktop/)
- [Git](https://git-scm.com/)

> **Nota:** No necesitas instalar PHP, Composer o Postgres localmente.

---

## ⚙️ Instalación

```bash
# Clona el repositorio
git clone https://github.com/tu-usuario/fisinova-core.git
cd fisinova-core

# Copia las variables de entorno
cp .env.example .env

# Construye y levanta los contenedores
docker compose up -d --build

# Instala las dependencias y genera la clave de aplicación
docker exec -it fisinova-core bash
composer install
php artisan key:generate
php artisan migrate
exit
