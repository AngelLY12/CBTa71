#!/bin/bash
set -e
echo "Iniciando worker de colas..."
php artisan queue:work redis --max-jobs=50 --sleep=3 --tries=3 --timeout=90 --backoff=5 --verbose &

php artisan schedule:list
echo "Iniciando scheduler..."
while true; do
    {
        echo "---- $(date '+%Y-%m-%d %H:%M:%S') Ejecutando schedule:run ----"
        php artisan schedule:run
        echo "---- Esperando 60 segundos ----"
    } | tee -a /var/www/storage/logs/scheduler.log
    sleep 60
done &
