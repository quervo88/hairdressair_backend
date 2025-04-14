FROM phpswoole/swoole:php8.2

# Szükséges csomagok (ha kell még)
RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev libpng-dev libonig-dev libxml2-dev libcurl4-openssl-dev

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# PHP beállítás (engedélyezzük a pcntl függvényeket)
COPY docker-php.ini /usr/local/etc/php/conf.d/docker-php.ini

# Projekt könyvtár
WORKDIR /var/www

# Projekt fájlok másolása
COPY . .

# Composer telepítés
RUN composer install --no-dev --optimize-autoloader

# Laravel cache
RUN php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear

# Port megnyitása
EXPOSE 8000

# Octane futtatás
CMD ["php", "artisan", "octane:start", "--server=swoole", "--host=0.0.0.0", "--port=8000"]
