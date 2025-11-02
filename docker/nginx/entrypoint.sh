#!/bin/sh
set -e

NGINX_CMD="nginx -g 'daemon off;'"
LARAVEL_URL="http://localhost/health"
CHECK_INTERVAL=10

$NGINX_CMD &
NGINX_PID=$!

while true; do
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$LARAVEL_URL" || echo 000)
    
    if [ "$HTTP_CODE" != "200" ]; then
        echo "Laravel no responde ($HTTP_CODE). Cerrando Nginx para que Railway reinicie..."
        kill $NGINX_PID
        exit 1
    fi

    sleep $CHECK_INTERVAL
done
