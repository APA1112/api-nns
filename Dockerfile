FROM dunglas/frankenphp:1-php8.3-alpine

WORKDIR /app

# 1. Instalamos extensiones de sistema y PHP
RUN apk add --no-cache \
    acl \
    file \
    gettext \
    git \
    postgresql-dev \
    && install-php-extensions \
    intl \
    zip \
    opcache \
    pdo_pgsql \
    bcmath \
    iconv

# 2. Instalamos Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. Copiamos el proyecto
COPY . .

# 4. Instalamos dependencias y preparamos el autoloader
RUN set -eux; \
    composer install --prefer-dist --no-dev --no-scripts --no-progress; \
    composer dump-autoload --classmap-authoritative --no-dev; \
    chmod +x bin/console

# 5. Variables de entorno para evitar que FrankenPHP fuerce HTTPS
ENV SERVER_NAME=:80
ENV HTTPS=off
ENV APP_ENV=prod

# 6. Script de entrada (Limpiamos caché al arrancar)
RUN printf "#!/bin/sh\n\
php bin/console cache:clear --no-warmup\n\
exec frankenphp php-server --listen :80\n" > /usr/local/bin/docker-entrypoint.sh && \
chmod +x /usr/local/bin/docker-entrypoint.sh

# Solo exponemos el puerto 80
EXPOSE 80

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]