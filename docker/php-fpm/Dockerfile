FROM php:7.3-fpm-alpine AS product 
WORKDIR /var/www/html
RUN set -x && \
  apk update  && \
  apk add --no-cache autoconf curl \
  freetype \
  libpng \
  libjpeg-turbo \
  freetype-dev \
  libpng-dev \
  jpeg-dev \
  libjpeg \
  libjpeg-turbo-dev  && \
  docker-php-ext-configure gd \
  --with-freetype-dir=/usr/lib/ \
  --with-png-dir=/usr/lib/ \
  --with-jpeg-dir=/usr/lib/ \
  --with-gd  && \
  docker-php-ext-install pdo_mysql gd   

RUN curl -sS https://getcomposer.org/installer | php && \
  mv composer.phar /usr/local/bin/composer && \
  chmod +x /usr/local/bin/composer

COPY ./src/database /var/www/html/database
COPY ./src/tests /var/www/html/tests
COPY ./src/composer.* /var/www/html/
RUN composer install --no-scripts
COPY ./src /var/www/html
RUN php artisan clear-compiled
RUN php artisan optimize

COPY ./docker/php-fpm/php.ini /usr/local/etc/php/

FROM product AS develop
RUN apk add --no-cache  build-base && \
  pecl install xdebug && \
  docker-php-ext-enable xdebug 