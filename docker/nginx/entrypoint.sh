#!/bin/sh
set -e
echo "Iniciando Nginx..."
exec nginx -g "daemon off;"
