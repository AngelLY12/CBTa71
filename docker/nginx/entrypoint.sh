#!/bin/sh
set -e

LARAVEL_HOST="cbta71.railway.internal"
LARAVEL_PORT=9000

echo "Esperando a Laravel antes de iniciar Nginx..."

for i in $(seq 1 10); do
  if nc -z "$LARAVEL_HOST" "$LARAVEL_PORT"; then
    echo "Laravel está listo en $LARAVEL_HOST:$LARAVEL_PORT"
    break
  fi

  echo "⏳ Esperando a Laravel... ($i/10)"
  sleep 18

  if [ "$i" -eq 10 ]; then
    echo "Laravel no respondió después de 3 minutos, iniciando Nginx de todos modos..."
  fi
done

echo "Iniciando Nginx..."
exec nginx -g "daemon off;"

