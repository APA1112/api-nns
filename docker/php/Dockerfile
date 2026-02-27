FROM php:8.3-fpm-alpine

# Instalar dependencias del sistema y extensiones de PHP necesarias
RUN apk add --no-cache \
    icu-dev \
    libpq-dev \
    zip \
    unzip \
    git

RUN docker-php-ext-install \
    intl \
    pdo \
    pdo_pgsql

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar archivos del proyecto
COPY . .

# Instalar dependencias de Symfony
RUN composer install --no-dev --optimize-autoloader

# Exponer el puerto
EXPOSE 80

CMD ["php-fpm"]