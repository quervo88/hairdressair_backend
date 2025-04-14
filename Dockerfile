FROM phpswoole/swoole:php8.2

# Alap csomagok (opcionális, ha szükségesek)
RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev libpng-dev libonig-dev libxml2-dev libcurl4-openssl-dev

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Projekt gyökér
WORKDIR /var/www

# Fájlok másolása
COPY . .

# Composer install
RUN composer install --no-dev --optimize-autoloader

# Laravel cache ürítés
RUN php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear

# Port megnyitása
EXPOSE 8000

# Octane indítása
CMD ["php", "artisan", "octane:start", "--server=swoole", "--host=0.0.0.0", "--port=8000"]
