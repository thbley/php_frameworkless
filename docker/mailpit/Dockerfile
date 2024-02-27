FROM alpine:3.19

RUN apk upgrade --no-cache \
    && ARCH=$([ $(uname -m) == "x86_64" ] && echo "amd64" || echo "arm64") \
    && wget -qO- https://github.com/axllent/mailpit/releases/latest/download/mailpit-linux-$ARCH.tar.gz | tar xvz -C /usr/local/bin mailpit \
    && adduser -u 1000 -H -D -S -G nogroup mailpit

USER mailpit:nogroup

EXPOSE 1025 8025

HEALTHCHECK none

CMD ["mailpit"]
