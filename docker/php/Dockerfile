FROM php:8.3.20-fpm

# System dependencies
RUN apt-get update && apt-get install -y \
    git curl unzip zip libpq-dev libzip-dev \
    && docker-php-ext-install pdo_pgsql zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Set recommended permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www

# Optional: add PHP OPcache for faster performance
RUN docker-php-ext-install opcache

CMD ["php-fpm"]
