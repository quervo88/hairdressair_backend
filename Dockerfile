FROM php:8.2-cli

# Alap csomagok + PHP extensionök
RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev libpng-dev libonig-dev libxml2-dev libcurl4-openssl-dev \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath \
    && pecl install swoole \
    && docker-php-ext-enable swoole

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Projekt gyökérmappa
WORKDIR /var/www

# Laravel fájlok bemásolása
COPY . .

# Composer csomagok telepítése
RUN composer install --optimize-autoloader --no-dev

# Laravel cache törlések (nem kötelező, de hasznos)
RUN php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear

# Port megnyitása
EXPOSE 8000

# Laravel Octane futtatása Swoole szerverrel
CMD ["php", "artisan", "octane:start", "--server=swoole", "--host=0.0.0.0", "--port=8000"]
