# Dockerfile
FROM php:8.2-fpm

# Telepítések
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    curl \
    git \
    nano \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Projekt fájlok
WORKDIR /var/www
COPY . .

# Jogosultság beállítása
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www

# Laravel cache, config és autoload optimalizálás (opcionális)
RUN composer install --no-dev --optimize-autoloader \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

EXPOSE 9000
CMD ["php-fpm"]
