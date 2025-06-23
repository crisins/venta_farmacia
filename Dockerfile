# Imagen base con PHP 8.2 y Apache
FROM php:8.2-apache

# Habilita extensiones necesarias (SQLite, pdo, etc.)
RUN docker-php-ext-install pdo pdo_sqlite

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
RUN echo "<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html/public

    <Directory /var/www/html/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>" > /etc/apache2/sites-available/000-default.conf
