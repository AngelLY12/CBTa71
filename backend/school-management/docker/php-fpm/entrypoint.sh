#!/bin/bash
set -e

echo "Iniciando configuración de Laravel..."

echo "Limpiando cachés de Laravel..."
php artisan optimize:clear || true
echo "Generando cache optimizado..."
php artisan optimize || true


echo "Ejecutando migraciones..."
php artisan migrate --force || { echo "Error al migrar"; exit 1; }

echo "Ejecutando seeders..."
php artisan db:seed --force || { echo "Error al ejecutar seeders"; exit 1; }

echo "Laravel preparado. Iniciando servicios..."

exec php-fpm
