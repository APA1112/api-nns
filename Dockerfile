# Base con PHP 8.3 y FrankenPHP
FROM dunglas/frankenphp:1-php8.3-alpine AS frankenphp_upstream

FROM frankenphp_upstream AS frankenphp_base

WORKDIR /app

# Instalar dependencias del sistema y extensiones de PHP necesarias para tu composer.json
RUN apk add --no-cache \
    acl \
    file \
    gettext \
    git \
    && install-php-extensions \
    intl \
    zip \
    opcache \
    pdo_pgsql \
    bcmath

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuración de PHP para producción
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Copiar archivos de dependencias primero para aprovechar la cache de Docker
COPY composer.json composer.lock symfony.lock ./
RUN set -eux; \
    composer install --prefer-dist --no-dev --no-scripts --no-progress; \
    composer clear-cache

# Copiar el resto del código
COPY . .

# Crear carpetas necesarias y dar permisos
RUN set -eux; \
    mkdir -p var/cache var/log; \
    setfacl -R -m u:www-data:rwX var; \
    setfacl -dR -m u:www-data:rwX var

# Limpiamos las carpetas de caché por si acaso
RUN rm -rf var/cache/* var/log/*

# FASE DE COMPILACIÓN: 
# Solo generamos el autoloader. NO ejecutamos bin/console aquí.
RUN set -eux; \
    composer dump-autoload --classmap-authoritative --no-dev; \
    chmod +x bin/console

# Creamos un pequeño script de entrada para ejecutar la caché al arrancar
RUN echo '#!/bin/sh' > /usr/local/bin/docker-entrypoint.sh && \
    echo 'php bin/console cache:clear --env=prod' >> /usr/local/bin/docker-entrypoint.sh && \
    echo 'exec frankenphp run --config /etc/caddy/Caddyfile' >> /usr/local/bin/docker-entrypoint.sh && \
    chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80
EXPOSE 443

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

# Ejecutar scripts de post-instalación (cache warmup e importmap)
# Usamos APP_RUNTIME_ENV y desactivamos el calentamiento de caché 
# que requiere DB para que el build no falle.
RUN set -eux; \
    export APP_ENV=prod; \
    export DATABASE_URL="postgresql://null:null@127.0.0.1:5432/null"; \
    # Instalamos sin ejecutar los scripts de composer.json para evitar el error
    composer dump-autoload --classmap-authoritative --no-dev; \
    # Calentamos la caché manualmente saltándonos la base de datos
    php bin/console cache:clear --no-warmup; \
    php bin/console cache:warmup; \
    chmod +x bin/console

# Exponer el puerto que usará Traefik (Dokploy)
EXPOSE 80
EXPOSE 443

CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]