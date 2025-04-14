FROM php:8.2-cli

# Rendszer csomagok + PostgreSQL driverhez szükséges libpq-dev
RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev libpng-dev libonig-dev libxml2-dev libcurl4-openssl-dev \
    libpq-dev \
    && docker-php-ext-install pdo_mysql pdo_pgsql mbstring zip exif pcntl bcmath sockets

# Composer másolása
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Alkalmazás könyvtár beállítása
WORKDIR /var/www

# Laravel fájlok másolása
COPY . .

# Composer install + migrációk automatikus futtatása
RUN composer install --no-dev --optimize-autoloader \
    && php artisan migrate --force

# Laravel cache ürítése (opcionális, de javasolt)
RUN php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear

# HTTP port megnyitása (Render figyeli a 8000-est)
EXPOSE 8000

# Laravel beépített szerver indítása
CMD php artisan serve --host=0.0.0.0 --port=8000
