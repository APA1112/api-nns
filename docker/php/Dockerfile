FROM dunglas/frankenphp:1-php8.3-alpine

# 1. Instalamos dependencias del sistema y extensiones de PHP
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    icu-dev \
    && docker-php-ext-install pdo pdo_pgsql zip intl

# 2. Instalamos Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# 3. COPIAMOS EL CÓDIGO (Paso crítico para Dokploy/Producción)
COPY . .

# 4. Instalamos dependencias de Symfony para producción
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --optimize-autoloader --no-scripts

# 5. Permisos para Symfony (Cache y Logs)
RUN chown -R www-data:www-data var/

# Exponemos el puerto (PHP-FPM usa el 9000 por defecto)
EXPOSE 9000