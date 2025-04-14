FROM php:8.2-cli

# rendszer csomagok és PHP extensionök
RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev libpng-dev libonig-dev libxml2-dev libonig-dev libcurl4-openssl-dev \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl gd

# Composer telepítése
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Projekt mappa
WORKDIR /var/www

COPY . .

# Oktán + composer
RUN composer install --optimize-autoloader --no-dev \
    && php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear

# Portot megnyitjuk
EXPOSE 8000

CMD ["php", "artisan", "octane:start", "--server=swoole", "--host=0.0.0.0", "--port=8000"]
