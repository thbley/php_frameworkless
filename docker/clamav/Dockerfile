FROM clamav/clamav:stable

RUN apk upgrade --no-cache \
  && apk add --no-cache clamav \
  && echo "DetectPUA yes" >> /etc/clamav/clamd.conf

COPY entrypoint.sh /entrypoint.sh

USER clamav:clamav

HEALTHCHECK none

ENTRYPOINT ["/entrypoint.sh"]
