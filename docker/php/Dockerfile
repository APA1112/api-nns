FROM php:8.3-fpm-alpine

# Instalar dependencias del sistema
RUN apk add --no-cache \
    icu-dev \
    libpq-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    oniguruma-dev

# Instalar extensiones de PHP necesarias para Symfony y Postgres
RUN docker-php-ext-install \
    intl \
    pdo \
    pdo_pgsql \
    mbstring \
    xml \
    opcache

# Instalar Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiamos archivos de configuración de dependencias primero para cachear capas
COPY composer.json composer.lock ./

# IMPORTANTE: Definimos que no corra scripts de post-instalación aún
# para evitar que falle si no encuentra la base de datos durante el build
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copiar el resto del código
COPY . .

# Finalizar la instalación de composer
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative
RUN composer run-script --no-dev post-install-cmd

EXPOSE 9000

CMD ["php-fpm"]