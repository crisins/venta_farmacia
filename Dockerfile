# Imagen base con PHP 8.2 y Apache
FROM php:8.2-apache

# Instala dependencias necesarias
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    unzip \
    curl \
    && docker-php-ext-install pdo pdo_sqlite

# Copia el contenido completo al contenedor
COPY . /var/www/html/

# Define la carpeta donde está el proyecto Laravel
WORKDIR /var/www/html/venta-gatito

# Instala Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Permitir ejecución de Composer como root
ENV COMPOSER_ALLOW_SUPERUSER=1

# Instala las dependencias PHP
RUN composer install --no-dev --optimize-autoloader

# Borra caché de config y views
RUN php artisan config:clear && \
    php artisan route:clear && \
    php artisan view:clear

# Reestablece permisos
RUN chmod -R 775 storage bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap/cache

# Establece permisos necesarios
RUN chown -R www-data:www-data storage bootstrap/cache

# Habilita el módulo rewrite de Apache
RUN a2enmod rewrite

# Configura Apache para servir Laravel desde /public
RUN echo '<VirtualHost *:80>\n\
    ServerAdmin webmaster@localhost\n\
    DocumentRoot /var/www/html/venta-gatito/public\n\
\n\
    <Directory /var/www/html/venta-gatito/public>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

RUN cp /var/www/html/venta-gatito/database/database.sqlite /tmp/database.sqlite
