FROM php:8.3-apache

FROM php:8.3-apache

# Apuntar DocumentRoot a public/
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Permitir acceso y .htaccess
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf \
 && sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/Require all denied/Require all granted/' /etc/apache2/apache2.conf

RUN a2enmod rewrite


# Dependencias del sistema
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libicu-dev libzip-dev \
    && docker-php-ext-install pdo_pgsql intl zip

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar proyecto
COPY . .

# Instalar dependencias PHP
RUN composer install --no-interaction --no-progress --prefer-dist

# Permisos
RUN chown -R www-data:www-data var

EXPOSE 80
