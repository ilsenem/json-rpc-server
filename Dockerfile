FROM php:8-cli-alpine as php
FROM mlocati/php-extension-installer as php-ext-installer
FROM composer:2 as composer

FROM php as runtime
ARG GID
ARG UID
COPY --from=composer /usr/bin/composer /usr/local/bin/
COPY --from=php-ext-installer /usr/bin/install-php-extensions /usr/local/bin/ipe
COPY ./docker/php/*.ini /usr/local/etc/php/conf.d/
RUN addgroup -S -g $GID host && adduser -S -D -G host -u $UID host && \
		apk add --no-cache --update make && \
    cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini && \
    ipe xdebug-3.1.5
WORKDIR /opt/project
VOLUME ["/opt/project"]
EXPOSE 8080
USER host:host
