FROM alpine:3.20

RUN apk upgrade --no-cache \
    && apk add --no-cache redis

USER redis:redis

EXPOSE 6379

VOLUME /var/lib/redis

HEALTHCHECK none

# see https://raw.githubusercontent.com/redis/redis/7.0/redis.conf
# "--activedefrag", "yes" only with Jemalloc
CMD ["redis-server", "--appendonly", "yes", "--maxmemory", "128M", "--save", "", \
     "--maxmemory-policy", "noeviction", "--dir", "/var/lib/redis", "--requirepass", "default"]
