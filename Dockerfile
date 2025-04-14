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

# Composer telepítés
RUN composer install --no-dev --optimize-autoloader

# Laravel cache ürítése
RUN php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear

# Port megnyitása
EXPOSE 8000

# Futtatás: migrációk lefuttatása, majd a szerver indítása
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8000
