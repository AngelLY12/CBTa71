set -e

LARAVEL_HOST="cbta71.railway.internal"
LARAVEL_PORT=9000
HEALTH_URL="https://$LARAVEL_HOST/api/up"

echo "Esperando a Laravel antes de iniciar Nginx..."

for i in $(seq 1 10); do
  if curl -fs "$HEALTH_URL" >/dev/null; then
    echo "Laravel HTTP está listo"
    break
  fi

  if nc -z "$LARAVEL_HOST" "$LARAVEL_PORT"; then
    echo "Laravel (PHP-FPM) está listo en $LARAVEL_HOST:$LARAVEL_PORT"
    break
  fi

  echo "Esperando a Laravel... ($i/20)"
  sleep 18

  if [ "$i" -eq 20 ]; then
    echo "Laravel no respondió después de 180 segundos. Iniciando Nginx de todos modos..."
  fi
done

echo "🚦 Iniciando Nginx..."
exec nginx -g "daemon off;"

