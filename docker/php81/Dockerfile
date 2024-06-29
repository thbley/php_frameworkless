FROM alpine:edge

# use 1 run command to reduce container image layers
RUN apk --no-cache upgrade \
    && apk --no-cache add php81-fpm php81-curl php81-pdo_mysql php81-pdo_pgsql php81-mbstring php81-opcache \
        php81-phar php81-tokenizer php81-dom php81-simplexml php81-xml php81-xmlwriter \
        php81-pecl-redis php81-pecl-pcov msmtp \
    && rm /etc/php81/conf.d/pcov.ini \
    && adduser -u 82 -h /var/www -D -S -G www-data www-data \
    && mkdir -m 0777 /var/coverage \
    && mkfifo -m 0777 /var/log/php \
    && mv /usr/bin/php81 /usr/bin/php \
    && wget -O /usr/bin/composer https://github.com/composer/composer/releases/latest/download/composer.phar \
    && chmod +x /usr/bin/composer

# php global setting
COPY ./php.ini /etc/php81/php.ini

# php fpm settings
COPY ./php-fpm.conf /etc/php81/php-fpm.d/www.conf

# msmtp settings
COPY msmtp.conf /etc/msmtprc

USER www-data:www-data

WORKDIR /var/www

EXPOSE 9000

VOLUME /var/coverage

STOPSIGNAL SIGQUIT

HEALTHCHECK none

CMD (tail -f /var/log/php &) ; (php-fpm81 -F)
