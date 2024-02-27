FROM mariadb:11-jammy

RUN apt-get -y update \
    && apt-get upgrade -y \
    && apt-get clean \
    && rm -rf /tmp/* /var/lib/apt/lists/* /var/cache/apt/archives/* \
    && ln -s /usr/bin/mariadb /usr/bin/mysql \
    && ln -s /usr/bin/mariadb-admin /usr/bin/mysqladmin

USER mysql:mysql

EXPOSE 3306 33060

HEALTHCHECK none

CMD ["mariadbd"]
