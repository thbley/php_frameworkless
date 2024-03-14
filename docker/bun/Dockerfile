FROM oven/bun:slim

run apt-get -y update \
    && apt-get upgrade -y \
    && apt-get -qq -y install --no-install-recommends patch \
    && apt-get clean \
    && rm -rf /tmp/* /var/lib/apt/lists/* /var/cache/apt/archives/*

USER bun:bun

WORKDIR /var/www

HEALTHCHECK none

ENV HOME=/tmp
