#!/bin/bash
set -e
echo "Iniciando configuración de Laravel..."

echo "Limpiando cachés de Laravel..."
php artisan config:clear || echo "No se pudo limpiar config"
php artisan cache:clear || echo "No se pudo limpiar cache"
php artisan route:clear || echo "No se pudo limpiar rutas"
php artisan optimize:clear || echo "No se pudo limpiar"

echo "Probando acceso a Google Drive..."
php -r "
use Illuminate\Support\Facades\Storage;
require __DIR__.'/vendor/autoload.php';
\$contents = Storage::disk('google')->listContents();
print_r(\$contents);
"

echo "Ejecutando backup manual..."
php artisan backup:run --only-db --only-to-disk=google --verbose || echo "Backup fallo manualmente"
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
        echo "---- Esperando 60 segundos ----"
    } | tee -a "$LOG_FILE"
    sleep 60
done
