#!/bin/sh
set -e

LARAVEL_URL=${LARAVEL_URL:-http://cbta71.railway.internal/api/health}
MAX_RETRIES=30
WAIT=10

echo "Esperando a que Laravel esté listo..."

i=0
until curl -s $LARAVEL_URL >/dev/null; do
    i=$((i+1))
    if [$i -ge $MAX_RETRIES]; then
        echo "Laravel no respondió después de $MAX_RETRIES intentos. Abortando."
        exit 1
    fi
    echo "Intento $i/$MAX_RETRIES: Laravel no responde todavía, esperando $WAIT segundos..."
    sleep $WAIT
done

echo "Laravel está listo. Iniciando Nginx..."
exec nginx -g "daemon off;"
