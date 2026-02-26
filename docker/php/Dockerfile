FROM dunglas/frankenphp:1-php8.3-alpine

# 1. Instalamos dependencias y extensiones (Añadimos icu-dev para intl)
RUN apk add --no-cache \
    bash \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    icu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo pdo_pgsql zip intl

# 2. Instalamos Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# 3. Copiamos solo los archivos de dependencias primero (Optimiza la caché de Docker)
COPY composer.json composer.lock ./

# 4. Instalamos dependencias saltándonos los chequeos de plataforma y scripts
# Esto evita que falle si falta una extensión menor o si no hay DB
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --optimize-autoloader --no-scripts --ignore-platform-reqs

# 5. Copiamos el resto del código
COPY . .

# 6. Creamos la carpeta var y damos permisos
RUN mkdir -p var && chown -R www-data:www-data var/

# Seteamos variables de entorno para producción
ENV APP_ENV=prod
ENV FRANKENPHP_CONFIG="worker ./public/index.php"

EXPOSE 80