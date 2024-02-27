FROM php:8.3-rc-fpm-alpine

RUN apk --no-cache upgrade \
    && apk --no-cache add libpq-dev msmtp \
    && pecl download redis pcov \
    && mkdir -p /usr/src/php/ext/redis /usr/src/php/ext/pcov \
    && tar xzf redis-*.tgz -C /usr/src/php/ext/redis --strip 1 \
    && tar xzf pcov-*.tgz -C /usr/src/php/ext/pcov --strip 1 \
    && rm *.tgz \
    # opcache
    && docker-php-ext-install -j$(nproc) pdo_mysql pdo_pgsql pcntl redis pcov opcache \
    && rm /usr/local/etc/php/conf.d/docker-php-ext-pcov.ini \
    && docker-php-source delete \
    && rm -rf /usr/src \
    && mkdir -m 0777 /var/coverage \
    && mkfifo -m 0777 /var/log/php \
    && wget -O /usr/bin/composer https://github.com/composer/composer/releases/latest/download/composer.phar \
    && chmod +x /usr/bin/composer

# php global setting
COPY ./php.ini /usr/local/etc/php/php.ini

# php fpm settings
COPY ./php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# msmtp settings
COPY msmtp.conf /etc/msmtprc

USER www-data:www-data

WORKDIR /var/www

EXPOSE 9000

VOLUME /var/coverage

STOPSIGNAL SIGQUIT

HEALTHCHECK none

ENV PHP_CS_FIXER_IGNORE_ENV=1

CMD (tail -f /var/log/php &) ; (php-fpm -F)
