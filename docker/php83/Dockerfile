FROM alpine:edge

# use 1 run command to reduce container image layers
RUN apk --no-cache upgrade \
    && apk --no-cache add php83-fpm php83-curl php83-pdo_mysql php83-pdo_pgsql php83-mbstring php83-opcache \
        php83-phar php83-tokenizer php83-dom php83-simplexml php83-xml php83-xmlwriter \
        php83-pecl-redis php83-pecl-pcov msmtp \
    && rm /etc/php83/conf.d/pcov.ini \
    && adduser -u 82 -h /var/www -D -S -G www-data www-data \
    && mkdir -m 0777 /var/coverage \
    && mkfifo -m 0777 /var/log/php \
    && mv /usr/bin/php83 /usr/bin/php \
    && wget -O /usr/bin/composer https://github.com/composer/composer/releases/latest/download/composer.phar \
    && chmod +x /usr/bin/composer

# php global setting
COPY ./php.ini /etc/php83/php.ini

# php fpm settings
COPY ./php-fpm.conf /etc/php83/php-fpm.d/www.conf

# msmtp settings
COPY msmtp.conf /etc/msmtprc

USER www-data:www-data

WORKDIR /var/www

EXPOSE 9000

VOLUME /var/coverage

STOPSIGNAL SIGQUIT

HEALTHCHECK none

ENV PHP_CS_FIXER_IGNORE_ENV=1

CMD (tail -f /var/log/php &) ; (php-fpm83 -F)
