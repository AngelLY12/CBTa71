#!/bin/sh
set -e

MAX_ATTEMPTS=20
SLEEP_TIME=5

echo "Esperando a que PHP-FPM esté listo..."

for i in $(seq 1 $MAX_ATTEMPTS); do
  if php -r "require '/var/www/html/artisan';" >/dev/null 2>&1; then
    echo "Laravel está listo (intento $i/$MAX_ATTEMPTS)"
    break
  fi

  echo "Laravel aún no está listo (intento $i/$MAX_ATTEMPTS)"
  sleep $SLEEP_TIME

  if [ "$i" -eq $MAX_ATTEMPTS ]; then
    echo "Laravel no respondió después de $((MAX_ATTEMPTS*SLEEP_TIME)) segundos. Iniciando Nginx de todos modos..."
  fi
done

echo "Iniciando Nginx..."
exec nginx -g "daemon off;"
