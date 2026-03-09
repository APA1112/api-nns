FROM dunglas/frankenphp:1-php8.3-alpine

WORKDIR /app

# 1. Instalamos extensiones mínimas
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
    bcmath

# 2. Instalamos Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. Copiamos el proyecto
COPY . .

# 4. Instalamos dependencias SIN ejecutar scripts de Symfony
RUN set -eux; \
    composer install --prefer-dist --no-dev --no-scripts --no-progress; \
    composer dump-autoload --classmap-authoritative --no-dev; \
    chmod +x bin/console

# 5. Creamos el script que se ejecutará al ARRANCAR (no al construir)
RUN printf "#!/bin/sh\n\
php bin/console cache:clear --env=prod\n\
exec frankenphp run --config /etc/caddy/Caddyfile\n" > /usr/local/bin/docker-entrypoint.sh && \
chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80
EXPOSE 443

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]