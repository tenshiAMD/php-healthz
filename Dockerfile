ARG PHP_VERSION=8.4
FROM php:${PHP_VERSION}-fpm-alpine

RUN apk --no-cache --update add bash ca-certificates curl git unzip wget zip linux-headers \
    && apk add --no-cache --virtual build-dependencies autoconf build-base g++ make \
    && docker-php-ext-install bcmath opcache \
    && docker-php-ext-enable bcmath opcache \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && chown www-data:www-data /usr/local/bin/composer \
    && apk del --purge autoconf build-dependencies g++ make \
    && chown -R www-data:www-data /var/www

WORKDIR /var/www
USER www-data:www-data
