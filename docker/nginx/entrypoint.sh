#!/bin/sh
set -e

SLEEP_TIME=180  
echo "Durmiendo $SLEEP_TIME segundos hasta que Laravel esté listo..."
sleep $SLEEP_TIME

echo "Iniciando Nginx..."
sleep 15
exec nginx -g "daemon off;"
