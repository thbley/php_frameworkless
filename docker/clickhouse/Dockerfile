FROM clickhouse/clickhouse-server:23-alpine

RUN apk --no-cache upgrade

USER clickhouse:clickhouse

EXPOSE 3306 8123

HEALTHCHECK none

CMD ["/entrypoint.sh"]
