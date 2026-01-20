FROM php:8.3-apache

# instalamos las dependencias del sistema, poreque el sistema interno de docker es linux
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libpq-dev \
        libicu-dev \
        libzip-dev \
    && docker-php-ext-install -j$(nproc) pdo_pgsql intl zip \
    && rm -rf /var/lib/apt/lists/*

# Instalamos Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Directorio de trabajo
WORKDIR /var/www/html

# Instalamos las dependencias de PHP (copiamos antes todo el código para que existan bin/console y evitar fallos de post-scripts)
COPY . .
RUN composer install --no-interaction --no-progress --prefer-dist --optimize-autoloader

# Ensure Symfony can write cache and logs when running as www-data
RUN mkdir -p var/cache var/log \
  && chown -R www-data:www-data var

# Script de entrada para correr migraciones y seed
COPY docker/app/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Configuramos Opcache para mejorar el rendimiento de PHP
RUN { \
  echo 'opcache.enable=1'; \
  echo 'opcache.enable_cli=1'; \
  echo 'opcache.memory_consumption=256'; \
  echo 'opcache.interned_strings_buffer=16'; \
  echo 'opcache.max_accelerated_files=20000'; \
  echo 'opcache.validate_timestamps=0'; \
} > /usr/local/etc/php/conf.d/opcache.ini

# Exponemos el puerto 9000 y arrancamos PHP-FPM
RUN a2enmod rewrite

