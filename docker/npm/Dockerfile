FROM alpine:3.19

RUN apk upgrade --no-cache \
    && apk add --no-cache npm nodejs patch \
    && npm cache clean --force \
    && adduser -u 1000 -h /var/www -D -S -G www-data www-data

USER www-data:www-data

WORKDIR /var/www

HEALTHCHECK none

ENV HOME=/tmp
