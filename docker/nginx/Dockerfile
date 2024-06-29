FROM alpine:3.20 as build

RUN apk upgrade --no-cache \
    && apk add --no-cache openssl \
    && openssl req -x509 -nodes -newkey ec -pkeyopt ec_paramgen_curve:secp384r1 -keyout /tmp/ca.key -out /tmp/ca.crt \
        -days 30 -sha384 -subj "/CN=nginx_ca" \
    && openssl req -nodes -newkey ec -pkeyopt ec_paramgen_curve:secp384r1 -keyout /tmp/nginx.key -out /tmp/nginx.csr \
        -sha384 -subj "/CN=nginx" \
    && openssl x509 -req -CA /tmp/ca.crt -CAkey /tmp/ca.key -in /tmp/nginx.csr -out /tmp/nginx.crt -days 30 -sha384 \
        -CAcreateserial \
        -extfile <(printf "authorityKeyIdentifier=keyid,issuer\nbasicConstraints=CA:FALSE\nsubjectAltName=DNS:nginx,IP:127.0.0.1")

FROM alpine:3.20

RUN apk upgrade --no-cache \
    && apk add --no-cache nginx \
    && sed -i "s/user nginx;//" /etc/nginx/nginx.conf

COPY --from=build --chown=nginx:nginx /tmp/nginx.crt /etc/ssl/certs/nginx.crt
COPY --from=build --chown=nginx:nginx /tmp/nginx.csr /etc/ssl/certs/nginx.csr
COPY --from=build --chown=nginx:nginx /tmp/ca.crt /etc/ssl/certs/ca.crt
COPY --from=build --chown=nginx:nginx /tmp/nginx.key /etc/ssl/private/nginx.key

USER nginx:nginx

EXPOSE 8080 443

HEALTHCHECK none

CMD ["nginx", "-g", "daemon off;"]
