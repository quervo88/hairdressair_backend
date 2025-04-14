FROM php:8.2-cli

# Rendszer csomagok
RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev libpng-dev libonig-dev libxml2-dev libcurl4-openssl-dev \
    && docker-php-ext-install pdo_mysql pdo_pgsql mbstring zip exif pcntl bcmath sockets


# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# App mappa
WORKDIR /var/www

# Fájlok
COPY . .

# Composer install
RUN composer install --no-dev --optimize-autoloader \
    && php artisan migrate --force

# Laravel cache ürítés
RUN php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear

# Port nyitása
EXPOSE 8000

# Laravel beépített webszerver indítása
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
