#!/bin/bash
set -e

echo "Iniciando worker de colas..."
php artisan queue:work redis --max-jobs=50 --sleep=3 --tries=3 --timeout=90 --backoff=5 --verbose &

echo "Mostrando tareas programadas..."
php artisan schedule:list

echo "Iniciando scheduler..."
LOG_FILE="/var/www/storage/logs/scheduler-$(date '+%Y-%m-%d').log"
while true; do
    {
        echo "---- $(date '+%Y-%m-%d %H:%M:%S') Ejecutando schedule:run ----"
        php artisan schedule:run
        echo "---- Esperando 10 minutos ----"
    } | tee -a "$LOG_FILE"
    sleep 600
done
