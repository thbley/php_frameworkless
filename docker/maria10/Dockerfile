FROM mariadb:10-jammy

RUN apt-get -y update \
    && apt-get upgrade -y \
    && apt-get clean \
    && rm -rf /tmp/* /var/lib/apt/lists/* /var/cache/apt/archives/*

USER mysql:mysql

EXPOSE 3306 33060

HEALTHCHECK none

CMD ["mariadbd"]
