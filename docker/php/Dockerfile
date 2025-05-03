FROM php:8.3.20-fpm
# System dependencies
RUN apt-get update && apt-get install -y \
    git curl unzip zip libpq-dev libzip-dev \
    && docker-php-ext-install pdo_pgsql zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Recommended for permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www
