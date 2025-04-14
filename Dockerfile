FROM php:8.2-cli

# Alap csomagok + extensionök + Swoole
RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev libpng-dev libonig-dev libxml2-dev libcurl4-openssl-dev \
    libssl-dev pkg-config \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath sockets \
    && pecl install swoole \
    && docker-php-ext-enable swoole

# Composer másolása
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# php.ini override (pcntl_* engedélyezése)
COPY docker-php.ini /usr/local/etc/php/conf.d/docker-php.ini

# Projekt gyökér
WORKDIR /var/www

# Fájlok bemásolása
COPY . .

# Composer install
RUN composer install --no-dev --optimize-autoloader

# Laravel cache ürítés
RUN php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear

# Port nyitása Octane számára
EXPOSE 8000

# Octane indítás
CMD ["php", "artisan",
