#!/bin/sh
set -e

LARAVEL_HOST="cbta71.railway.internal"
LARAVEL_PORT=9000
HEALTH_URL="https://$LARAVEL_HOST/api/up"
MAX_ATTEMPTS=20
SLEEP_TIME=10

echo "Esperando a que Laravel esté listo..."

for i in $(seq 1 $MAX_ATTEMPTS); do
  if curl -fs "$HEALTH_URL" >/dev/null 2>&1; then
    echo "Laravel HTTP está listo (intento $i/$MAX_ATTEMPTS)"
    break
  fi

  echo "Esperando a Laravel... (intento $i/$MAX_ATTEMPTS)"
  sleep $SLEEP_TIME

  if [ "$i" -eq $MAX_ATTEMPTS ]; then
    echo "Laravel no respondió después de $((MAX_ATTEMPTS*SLEEP_TIME)) segundos. Iniciando Nginx de todos modos..."
  fi
done

echo "Iniciando Nginx..."
exec nginx -g "daemon off;"