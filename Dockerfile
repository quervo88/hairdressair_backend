FROM php:8.2-cli

# Alap csomagok telepítése
RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev libpng-dev libonig-dev libxml2-dev libonig-dev libcurl4-openssl-dev \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath

# Composer telepítése
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Laravel app root
WORKDIR /var/www

# Fájlok másolása
COPY . .

# Composer futtatása
RUN composer install --optimize-autoloader --no-dev

# Laravel cache parancsok (nem kötelező, de ajánlott)
RUN php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear

# Port megnyitása Octane-hoz
EXPOSE 8000

# Octane indítása
CMD ["php", "artisan", "octane:start", "--server=swoole", "--host=0.0.0.0", "--port=8000"]
