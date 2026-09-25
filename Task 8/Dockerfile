FROM serversideup/php:8.2-fpm-nginx-alpine

WORKDIR /var/www/html

USER root
RUN install-php-extensions pdo_mysql bcmath

COPY --chown=www-data:www-data . .

USER www-data

# Jalankan composer install saat build
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

ENV AUTORUN_ENABLED="true"
