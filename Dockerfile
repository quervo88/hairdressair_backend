FROM php:8.2-cli

# Frissítés és szükséges csomagok
RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev libpng-dev libonig-dev libxml2-dev \
    libcurl4-openssl-dev pkg-config libssl-dev \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath

# Swoole telepítése
RUN pecl install swoole \
    && docker-php-ext-enable swoole

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Mappa beállítás
WORKDIR /var/www

# Fájlok másolása
COPY . .

# Composer install
RUN composer install --no-dev --optimize-autoloader

# Laravel cache ürítés (biztonságos deployhoz)
RUN php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear

# Port megnyitás
EXPOSE 8000

# Octane indítás Swoole-al
CMD ["php", "artisan", "octane:start", "--server=swoole", "--host=0.0.0.0", "--port=8000"]
