#!/bin/bash
set -e

# Esperar a que la DB esté lista (opcional pero recomendado)
until php artisan migrate:status >/dev/null 2>&1; do
    echo "Esperando a la base de datos..."
    sleep 2
done

# Ejecutar migraciones y seeders
php artisan migrate --force
php artisan db:seed --force

# Iniciar PHP-FPM
exec php-fpm
