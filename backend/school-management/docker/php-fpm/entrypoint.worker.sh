#!/bin/bash
set -e
echo "Iniciando worker de colas..."
php artisan queue:work redis --max-jobs=50 --sleep=3 --tries=3 --timeout=90 --backoff=5 --verbose &

echo "Iniciando scheduler..."
while true; do
    php artisan schedule:run 2>&1 | tee -a /var/www/storage/logs/scheduler.log
    sleep 60
done &
