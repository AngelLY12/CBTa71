#!/bin/bash
set -e
echo "APP_ROLE=${APP_ROLE:-undefined}"

if [ "$APP_ROLE" = "app" ]; then
  echo "Esperando base de datos..."
  until php artisan migrate:status >/dev/null 2>&1; do
    sleep 3
  done
fi

if [ "$APP_ROLE" = "app" ]; then
  echo "Inicializando Laravel (APP)..."

  echo "Limpiando cachés..."
  php artisan optimize:clear || true

  echo "Ejecutando migraciones..."
  php artisan migrate --force

  echo "Ejecutando seeders..."
  php artisan db:seed --force || true
fi

echo "Laravel listo. Iniciando proceso principal..."

exec "$@"
