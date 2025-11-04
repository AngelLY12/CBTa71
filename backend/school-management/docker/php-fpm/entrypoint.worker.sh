#!/bin/bash
set -e
echo "Iniciando worker de colas..."
php artisan queue:work redis --sleep=3 --tries=3 --timeout=90 --backoff=5 --verbose &

echo "Iniciando scheduler..."
while true; do
    php artisan schedule:run >> /var/www/storage/logs/scheduler.log 2>&1
    sleep 60
done &
