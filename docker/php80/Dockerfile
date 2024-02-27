FROM alpine:3.16

# use 1 run command to reduce container image layers
RUN echo "http://dl-cdn.alpinelinux.org/alpine/edge/testing" >> /etc/apk/repositories \
    && echo "http://dl-cdn.alpinelinux.org/alpine/edge/main" >> /etc/apk/repositories \
    && apk --no-cache upgrade \
    && apk --no-cache add php8-fpm php8-curl php8-pdo_mysql php8-pdo_pgsql php8-mbstring php8-opcache \
        php8-phar php8-tokenizer php8-dom php8-simplexml php8-xml php8-xmlwriter \
        php8-pecl-redis msmtp \
    && adduser -u 82 -h /var/www -D -S -G www-data www-data \
    && mkdir -m 0777 /var/coverage \
    && mkfifo -m 0777 /var/log/php \
    && mv /usr/bin/php8 /usr/bin/php \
    && wget -O /usr/bin/composer https://github.com/composer/composer/releases/latest/download/composer.phar \
    && chmod +x /usr/bin/composer

# php global setting
COPY ./php.ini /etc/php8/php.ini

# php fpm settings
COPY ./php-fpm.conf /etc/php8/php-fpm.d/www.conf

# msmtp settings
COPY msmtp.conf /etc/msmtprc

USER www-data:www-data

WORKDIR /var/www

EXPOSE 9000

VOLUME /var/coverage

STOPSIGNAL SIGQUIT

HEALTHCHECK none

CMD (tail -f /var/log/php &) ; (php-fpm8 -F)
