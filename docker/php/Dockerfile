FROM php:8.3-fpm

# Instalamos dependencias del sistema y extensiones de PHP
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql zip

# Instalamos Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Instalamos el Symfony CLI para ayudarnos con el servidor local
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

WORKDIR /var/www/html