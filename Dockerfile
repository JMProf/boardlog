# syntax=docker/dockerfile:1
FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql && a2enmod rewrite

# App code
COPY ./public /var/www/html/
COPY ./app /var/www/app
COPY ./config /var/www/config

# Default envs (can be overridden by compose)
ENV APP_URL=http://localhost:8080     DB_HOST=db     DB_PORT=3306     DB_NAME=boardlog     DB_USER=boardlog     DB_PASS=boardlog     ADMIN_USERNAME=admin     ADMIN_PASSWORD=admin

# Apache docroot permissions (optional depending on host uid/gid)
RUN chown -R www-data:www-data /var/www/html /var/www/app /var/www/config

# .htaccess for URL handling (optional if already in repo)
# COPY ./public/.htaccess /var/www/html/.htaccess
