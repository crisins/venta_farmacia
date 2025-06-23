# Imagen base
FROM php:8.2-apache

# Instala extensiones necesarias
RUN apt-get update && apt-get install -y libsqlite3-dev unzip curl && \
    docker-php-ext-install pdo pdo_sqlite

# Copia los archivos del proyecto al contenedor
COPY . /var/www/html/

# Establece el directorio de trabajo
WORKDIR /var/www/html

# Instala Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Permitir ejecutar Composer como root
ENV COMPOSER_ALLOW_SUPERUSER=1

# Instala dependencias PHP del proyecto
RUN composer install --no-dev --optimize-autoloader

# Establece permisos para Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Habilita módulo rewrite de Apache
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
