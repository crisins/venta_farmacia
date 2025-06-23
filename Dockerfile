FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libsqlite3-dev unzip curl \
    && docker-php-ext-install pdo pdo_sqlite

COPY . /var/www/html/

WORKDIR /var/www/html/venta-gatito

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --optimize-autoloader

# Limpieza de cache
RUN php artisan config:clear && php artisan route:clear && php artisan view:clear

# Permisos
RUN chown -R www-data:www-data /var/www/html/venta-gatito
RUN chmod -R 775 /var/www/html/venta-gatito/storage /var/www/html/venta-gatito/bootstrap/cache

# Copiar la base de datos SQLite al /tmp (necesario para Render)
RUN cp /var/www/html/venta-gatito/database/database.sqlite /tmp/database.sqlite
RUN chown www-data:www-data /tmp/database.sqlite
RUN chmod 664 /tmp/database.sqlite

# Configuración Apache
RUN a2enmod rewrite
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/venta-gatito/public\n\
    <Directory /var/www/html/venta-gatito/public>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf
