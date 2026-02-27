#!/bin/bash
set -e

# Optimizaciones de Symfony
php bin/console cache:clear
php bin/console cache:warmup

# Ejecutar migraciones si usas base de datos (opcional)
# php bin/console doctrine:migrations:migrate --no-interaction

echo "🚀 Iniciando FrankenPHP..."

# Comando oficial para arrancar FrankenPHP dentro de Railpack
exec docker-php-entrypoint --config /Caddyfile --adapter caddyfile 2>&1