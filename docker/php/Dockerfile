# Usamos la imagen oficial de FrankenPHP con PHP 8.3 y Alpine (ligera y rápida)
FROM dunglas/frankenphp:1-php8.3-alpine

# Instalamos las dependencias usando 'apk' (el estándar en Alpine)
RUN apk add --no-cache \
    bash \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    icu-dev \
    && docker-php-ext-install pdo pdo_pgsql zip intl

# Instalamos Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuramos el directorio de trabajo
WORKDIR /app

# Copiamos los archivos del proyecto (esto es vital para el VPS)
COPY . .

# Instalamos dependencias de Symfony para producción
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Ajustamos permisos para que Symfony pueda escribir en cache y logs
RUN chown -R www-data:www-data var/

# FrankenPHP escucha por defecto en el puerto 80, pero Dokploy suele 
# preferir que definas uno claro. Exponemos el 80.
EXPOSE 80

# No hace falta CMD, la imagen de FrankenPHP ya sabe qué hacer.