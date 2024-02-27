#!/bin/sh

set -eu

touch /var/log/clamav/clamd.log
tail -f /var/log/clamav/*.log &

freshclam --quiet

clamd &

while [ ! -S /tmp/clamd.sock ]; do
    sleep 1
    echo -ne `date`"\r"
done

clamdscan --multiscan /var/www
