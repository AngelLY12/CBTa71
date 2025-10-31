#!/bin/bash
set -e

echo "Ajustando permisos de Laravel..."
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

echo "Probando conexión a la base de datos..."
php -r "
\$host = getenv('DB_HOST');
\$port = getenv('DB_PORT');
\$db = getenv('DB_DATABASE');
\$user = getenv('DB_USERNAME');
\$pass = getenv('DB_PASSWORD');

echo \"Conectando a \$host:\$port (\$db)...\\n\";
try {
    \$pdo = new PDO(\"mysql:host=\$host;port=\$port;dbname=\$db\", \$user, \$pass);
    echo \"Conexión exitosa a la base de datos.\\n\";
} catch (Exception \$e) {
    echo \"Error de conexión: \" . \$e->getMessage() . \"\\n\";
    exit(1);
}
"

echo "Probando conexión a Redis..."
php -r "
\$host = getenv('REDIS_HOST') ?: '127.0.0.1';
\$port = getenv('REDIS_PORT') ?: 6379;

echo \"Conectando a Redis en \$host:\$port...\\n\";

try {
    \$redis = new Redis();
    if (!\$redis->connect(\$host, \$port, 5)) {
        throw new Exception('No se pudo conectar al servidor Redis.');
    }
    echo \"Conexión exitosa a Redis.\\n\";
} catch (Exception \$e) {
    echo \"Error de conexión a Redis: \" . \$e->getMessage() . \"\\n\";
    exit(1);
}
"

echo "Limpiando cachés de Laravel..."
php artisan config:clear || echo "No se pudo limpiar config"
php artisan cache:clear || echo "No se pudo limpiar cache"
php artisan route:clear || echo "No se pudo limpiar rutas"

echo "Ejecutando migraciones..."
php artisan migrate --force || { echo "Error al migrar"; exit 1; }

echo "Ejecutando seeders..."
php artisan db:seed --force || { echo "Error al ejecutar seeders"; exit 1; }

echo "Últimas líneas del log de Laravel:"
tail -n 40 storage/logs/laravel.log || echo "No hay log aún."

echo "Todo listo, iniciando aplicación laravel..."
exec php-fpm

