#!/bin/sh
set -e

LARAVEL_URL="http://cbta71.railway.internal:9000/api/up" # puedes crear un endpoint /health que devuelva 200
MAX_ATTEMPTS=20
SLEEP_TIME=10

echo "Esperando a que Laravel esté listo..."

for i in $(seq 1 $MAX_ATTEMPTS); do
  HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$LARAVEL_URL" || echo 000)
  
  if [ "$HTTP_CODE" -eq 200 ]; then
    echo "Laravel está listo (intento $i/$MAX_ATTEMPTS)"
    break
  fi

  echo "Laravel aún no responde ($HTTP_CODE) (intento $i/$MAX_ATTEMPTS)"
  sleep $SLEEP_TIME

  if [ "$i" -eq $MAX_ATTEMPTS ]; then
    echo "Laravel no respondió después de $((MAX_ATTEMPTS*SLEEP_TIME)) segundos. Iniciando Nginx de todos modos..."
  fi
done

sleep $SLEEP_TIME

echo "Iniciando Nginx..."
exec nginx -g "daemon off;"
