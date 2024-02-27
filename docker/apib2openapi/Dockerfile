# see https://github.com/kminami/apib2swagger

FROM alpine:3.19

RUN apk upgrade --no-cache \
    && apk add --no-cache npm nodejs \
    && npm install --global apib2swagger \
    && npm cache clean --force \
    && apk del npm \
    && adduser -u 1000 -h /var/www -D -S -G www-data www-data

USER www-data:www-data

HEALTHCHECK none

ENTRYPOINT ["apib2swagger"]
