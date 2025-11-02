#!/bin/bash
set -e

echo "Iniciando configuración de Laravel..."

echo "Limpiando cachés de Laravel..."
php artisan config:clear || echo "No se pudo limpiar config"
php artisan cache:clear || echo "No se pudo limpiar cache"
php artisan route:clear || echo "No se pudo limpiar rutas"
php artisan optimize:clear || echo "No se pudo limpiar"

echo "Generando documentación Swagger..."
php artisan l5-swagger:generate || echo "No se pudo generar la documentación"
php artisan route:list | grep documentation || echo "No se encontró la ruta de documentación"

echo "Ejecutando migraciones..."
php artisan migrate --force || { echo "Error al migrar"; exit 1; }

echo "Ejecutando seeders..."
php artisan db:seed --force || { echo "Error al ejecutar seeders"; exit 1; }

echo "Laravel preparado. Iniciando servicios..."

exec php-fpm
