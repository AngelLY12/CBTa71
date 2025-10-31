#!/bin/bash
set -e

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

echo "Ejecutando migraciones..."
php artisan migrate --force || { echo "Error al migrar"; exit 1; }

echo "Ejecutando seeders..."
php artisan db:seed --force || { echo "Error al ejecutar seeders"; exit 1; }

echo "Todo listo, iniciando PHP-FPM..."
exec php-fpm

