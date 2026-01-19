#!/bin/bash
set -e

echo "Iniciando worker de Laravel..."

echo "Limpiando cachés..."
php artisan optimize:clear || echo "No se pudo limpiar"

echo "Tareas programadas:"
php artisan schedule:list || true

echo "⚙Iniciando worker + scheduler..."

while true; do
  echo "---- $(date '+%Y-%m-%d %H:%M:%S') Ejecutando queue:work ----"

  php artisan queue:work redis \
    --queue=cache,high,emails,low,default,notifications,processing \
    --sleep=3 \
    --tries=3 \
    --timeout=120 \
    --memory=256 \
    --max-jobs=100 \
    --max-time=3600

  echo "Worker reiniciado (memoria / señal / max-jobs)"

  echo "Ejecutando scheduler..."
  php artisan schedule:run --no-interaction

  sleep 60
done
