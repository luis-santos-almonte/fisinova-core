# Fisinova Core

Sistema de gestión para Unidades de Medicina Física y Rehabilitación, desarrollado con Laravel, PostgreSQL y Docker.

## 🚀 Requisitos

-   Composer
-   Git
-   PHP 8x

## 🛠 Instalación rápida

- Clona el repositorio
- Crear el .env a partir del example
```bash
copy .env.example .env
```
- Instalar el vendor usando composer install
```bash
componser install
```
- Generar la nueva Key del proyecto en el .env
```bash
php artisan key:generate
```
- Ejecutar migraciones
```bash
php artisan migrate
```
- Correr el sistema
```bash
php artisan serve
```

## ⚙️ Herramientas usadas

    Laravel (PHP 8.4x)

    PostgreSQL 17

    Composer

## 🧪 Comandos útiles

Si quieres ejecutar los seeders
```bash
php artisan db:seed
```

Si quieres volver a migrar y realizar los seeders
```bash
php artisan migrate:fresh --seed
```

Si quieres limpiar la cache del sistema
```bash
php artisan cache:clear
```