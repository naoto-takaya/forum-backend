FROM php:7.3-fpm-alpine
ADD ./docker/php-fpm/php.ini /usr/local/etc/php/
RUN apk update \
&& apk add autoconf build-base \
&& pecl install xdebug \
&& docker-php-ext-enable xdebug \
&& docker-php-ext-install pdo_mysql \
&& apk add composer 
