FROM alpine:edge

# use 1 run command to reduce container image layers
RUN apk --no-cache upgrade \
    && apk --no-cache add php82-fpm php82-curl php82-pdo_mysql php82-pdo_pgsql php82-mbstring php82-opcache \
        php82-phar php82-tokenizer php82-dom php82-simplexml php82-xml php82-xmlwriter \
        php82-pecl-redis php82-pecl-pcov msmtp \
    && rm /etc/php82/conf.d/pcov.ini \
    && adduser -u 82 -h /var/www -D -S -G www-data www-data \
    && mkdir -m 0777 /var/coverage \
    && mkfifo -m 0777 /var/log/php \
    && mv /usr/bin/php82 /usr/bin/php \
    && wget -O /usr/bin/composer https://github.com/composer/composer/releases/latest/download/composer.phar \
    && chmod +x /usr/bin/composer

# php global setting
COPY ./php.ini /etc/php82/php.ini

# php fpm settings
COPY ./php-fpm.conf /etc/php82/php-fpm.d/www.conf

# msmtp settings
COPY msmtp.conf /etc/msmtprc

USER www-data:www-data

WORKDIR /var/www

EXPOSE 9000

VOLUME /var/coverage

STOPSIGNAL SIGQUIT

HEALTHCHECK none

CMD (tail -f /var/log/php &) ; (php-fpm82 -F)
