# Etapa 1: Build environment
FROM php:8.4-fpm AS builder

# Instalar dependencias del sistema y extensiones PHP
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libonig-dev libxml2-dev zip unzip git curl nginx \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && pecl install redis \
    && docker-php-ext-enable redis

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copiar todo el proyecto
COPY . .

# Instalar dependencias de Laravel
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Copiar configuración de Nginx
COPY ./docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Copiar entrypoint personalizado
COPY ./docker/php-fpm/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Limpiar y cachear Laravel
RUN php artisan config:cache && php artisan route:cache && php artisan view:cache

# Exponer el puerto de Nginx
EXPOSE 80

# Usar tu entrypoint para iniciar Laravel
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

