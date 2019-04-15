FROM php:7.3-fpm-alpine

RUN apk update && apk add build-base

RUN apk add zlib-dev git zip libzip libzip-dev autoconf \
  && docker-php-ext-install zip \
  && docker-php-ext-install bcmath \
  && pecl install mongodb \
  && docker-php-ext-enable mongodb;

WORKDIR /app
COPY ["composer.json", "composer.lock", "./"]
COPY . ./

RUN curl -sS https://getcomposer.org/installer | php \
        && mv composer.phar /usr/local/bin/ \
        && ln -s /usr/local/bin/composer.phar /usr/local/bin/composer

RUN composer install --prefer-source --no-interaction \
  && cp config/autoload/local.php.docker.dist config/autoload/local.php

ENV PATH="~/.composer/vendor/bin:./vendor/bin:${PATH}"