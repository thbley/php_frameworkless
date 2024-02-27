PHP Frameworkless Micro Service Example
----------------------------------------

#### Development

    # remove containers/images/volumes
    docker compose down --remove-orphans
    docker images purge -a
    docker volume prune -a
    docker container prune
    docker builder prune -a
    docker system prune -a

    # access/error logs
    docker compose logs -f

    # start shell in php container
    docker compose exec php sh
    docker compose exec -u $(id -u) php sh

    # start mysql client
    docker compose exec mysql mysql -u root -proot tasks
    docker compose exec mysql mysql -u root -proot tasks -e "select * from task"
    docker compose exec mysql mysql -u root -proot tasks -e "select * from task_queue"
    docker compose exec mysql mysql -u root -proot tasks -s -e \
        "select event_time, convert(argument using utf8mb4) from mysql.general_log order by event_time desc limit 40"

    # start clickhouse client
    docker compose exec clickhouse clickhouse-client
    docker compose exec mysql mysql -hclickhouse -u root -proot tasks
    docker compose exec mysql mysql -hclickhouse -u root -proot tasks -e "select * from mysql_tasks.task"
    docker compose exec mysql mysql -hclickhouse -u root -proot tasks -e "select * from mysql_tasks_task_view"

    # start redis client
    docker compose exec redis redis-cli
    docker compose exec redis redis-cli INFO memory | grep -E "used_memory|maxmemory|frag" | grep -E "human|bytes|run"
    docker compose exec redis redis-cli INFO clients
    docker compose exec redis redis-cli XINFO STREAM tasks
    docker compose exec redis redis-cli XINFO GROUPS tasks
    docker compose exec redis redis-cli XINFO CONSUMERS tasks mygroup
    docker compose exec redis redis-cli XPENDING tasks mygroup
    docker compose exec redis redis-cli XREAD COUNT 10 STREAMS tasks 0
    docker compose exec redis redis-cli XREAD BLOCK 0 STREAMS tasks $
    docker compose exec redis redis-cli XTRIM tasks MAXLEN 0
    docker compose exec redis redis-cli DEL tasks
    docker compose exec redis redis-cli KEYS \*
    docker compose exec redis redis-cli BGREWRITEAOF
    docker compose exec redis redis-cli INFO LATENCYSTATS
    docker compose exec redis redis-cli MEMORY PURGE
    docker compose exec redis redis-cli MEMORY DOCTOR
    docker compose exec redis redis-cli SLOWLOG GET

    # playwright ui (URL http://127.0.0.1:8081)
    docker compose -f docker-compose-tasks-e2e.yml run --rm -P playwright_ui

    # run playwright test with html report and videos (URL http://127.0.0.1:8081)
    docker compose -f docker-compose-tasks-e2e.yml run --rm -P playwright_ui --project=chromium \
      -c playwright-html.config.js

    # monitoring
    docker compose -f docker-compose-tasks-be.yml run --rm cli fpm_status.php

    # import and trust self signed certificate on host for ubuntu
    true | openssl s_client -connect 127.0.0.1:443 -servername nginx 2>/dev/null | openssl x509 >/tmp/nginx.crt
    sudo cp /tmp/nginx.crt /usr/local/share/ca-certificates/nginx.crt && sudo update-ca-certificates

    # import and trust self signed certificate on firefox, chrome, edge
    true | openssl s_client -connect 127.0.0.1:443 -servername nginx 2>/dev/null | openssl x509 >/tmp/nginx.crt
    for file in $(find ~/ -name "cert9.db"); do
        echo ${file}; certutil -A -n "nginx" -t "PCw,," -i /tmp/nginx.crt -d sql:$(dirname ${file})
    done

    # remove self signed certificate on host for ubuntu and firefox, chrome, edge
    sudo rm /usr/local/share/ca-certificates/nginx.crt && sudo update-ca-certificates
    for file in $(find ~/ -name "cert9.db"); do echo ${file}; certutil -D -n "nginx" -d sql:$(dirname ${file}); done
