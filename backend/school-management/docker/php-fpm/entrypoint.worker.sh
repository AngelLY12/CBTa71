#!/bin/bash
set -e
echo "Iniciando configuración de Laravel..."

echo "Limpiando cachés de Laravel..."
php artisan config:clear || echo "No se pudo limpiar config"
php artisan cache:clear || echo "No se pudo limpiar cache"
php artisan route:clear || echo "No se pudo limpiar rutas"
php artisan optimize:clear || echo "No se pudo limpiar"

echo "Iniciando workers especializados..."

# 1. WORKER CACHE - Alta prioridad, rápido, muchos reintentos
php artisan queue:work redis \
  --queue=cache,high \
  --max-jobs=100 \
  --sleep=1 \
  --tries=5 \
  --timeout=30 \
  --backoff=2 \
  --memory=128 \
  --name=cache-worker &

# 2. WORKER EMAILS - Baja prioridad, lento, pocos reintentos
php artisan queue:work redis \
  --queue=emails,low \
  --max-jobs=30 \
  --sleep=5 \
  --tries=2 \
  --timeout=180 \
  --backoff=10 \
  --memory=256 \
  --name=email-worker &

# 3. WORKER DEFAULT - Procesamiento general
php artisan queue:work redis \
  --queue=default,notifications,processing \
  --max-jobs=50 \
  --sleep=3 \
  --tries=3 \
  --timeout=90 \
  --backoff=5 \
  --memory=192 \
  --name=default-worker &


echo "Workers iniciados. Mostrando estado..."
sleep 2
php artisan queue:monitor --queue=cache,emails,default

echo "Mostrando tareas programadas..."
php artisan schedule:list

echo "Iniciando scheduler..."
LOG_FILE="/var/www/storage/logs/scheduler-$(date '+%Y-%m-%d').log"
while true; do
    {
        echo "---- $(date '+%Y-%m-%d %H:%M:%S') Ejecutando schedule:run ----"
        php artisan schedule:run
        echo "---- Esperando 60 segundos ----"
    } | tee -a "$LOG_FILE"
    sleep 60
done
