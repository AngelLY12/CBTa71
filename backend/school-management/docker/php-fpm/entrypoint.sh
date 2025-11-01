#!/bin/bash
set -e

echo "Iniciando configuración de Laravel..."

echo "Limpiando cachés de Laravel..."
php artisan config:clear || echo "No se pudo limpiar config"
php artisan cache:clear || echo "No se pudo limpiar cache"
php artisan route:clear || echo "No se pudo limpiar rutas"

echo "Ejecutando migraciones..."
php artisan migrate --force || { echo "Error al migrar"; exit 1; }

echo "Ejecutando seeders..."
php artisan db:seed --force || { echo "Error al ejecutar seeders"; exit 1; }

echo "Puerto de Laravel: ${PORT:-80}"

echo "Laravel preparado. Iniciando servicios..."

# Iniciar PHP-FPM en background
service php8.4-fpm start

# Iniciar Nginx en foreground (Railway necesita un proceso principal)
exec nginx -g "daemon off;"
