# Imagen base con PHP 8.2 y Apache
FROM php:8.2-apache

# Habilita extensiones necesarias (SQLite, pdo, etc.)
RUN apt-get update && apt-get install -y libsqlite3-dev && \
    docker-php-ext-install pdo pdo_sqlite


# Copia los archivos del proyecto al contenedor
COPY . /var/www/html/

# Establece el directorio de trabajo
WORKDIR /var/www/html

# Instala Composer
RUN apt-get update && \
    apt-get install -y unzip curl && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Instala dependencias PHP del proyecto
RUN composer install --no-dev --optimize-autoloader

# Establece permisos de Laravel (storage y bootstrap/cache)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Habilita reescritura de URLs en Apache (necesario para Laravel)
RUN a2enmod rewrite

# Configura Apache para servir desde /public
RUN echo '<VirtualHost *:80>\n\
    ServerAdmin webmaster@localhost\n\
    DocumentRoot /var/www/html/public\n\
\n\
    <Directory /var/www/html/public>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

