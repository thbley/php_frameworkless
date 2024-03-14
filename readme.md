PHP Frameworkless Micro Service Example
----------------------------------------

[![Actions Build Status](https://github.com/thomasbley/example_tasks_php/workflows/build/badge.svg?branch=master)](https://github.com/thomasbley/example_tasks_php/actions)

Example micro service to provide a REST API to manage customer tasks.

The backend is written in PHP without a standard framework, stores data in MySQL/MariaDB,
uses ClickHouse for analytics and Redis as event stream.

The frontend is written as an SPA in Typed JavaScript using ES Modules and Alpine.js.

#### Setup

    # clone repository
    git clone --depth=1 git@github.com:thbley/php_frameworkless.git
    cd php_frameworkless

    # build container
    docker compose -f docker-compose-tasks-be.yml -f docker-compose-tasks-fe.yml build --pull --no-cache --parallel

    # setup composer
    mkdir -m 0777 tasks_be/src/vendor tasks_be/tests/vendor
    chmod 0666 tasks_be/composer.json tasks_be/composer.lock tasks_be/tests/composer.json tasks_be/tests/composer.lock
    docker compose -f docker-compose-tasks-be.yml run -u $(id -u) --rm composer
    docker compose -f docker-compose-tasks-be.yml run -u $(id -u) --rm composer_tests

    # setup npm
    mkdir -m 0777 tasks_fe/src/node_modules tasks_fe/tests/node_modules
    chmod 0666 tasks_fe/src/package.json tasks_fe/src/package-lock.json tasks_fe/tests/package.json tasks_fe/tests/package-lock.json
    docker compose -f docker-compose-tasks-fe.yml run -u $(id -u) --rm npm
    docker compose -f docker-compose-tasks-fe.yml run -u $(id -u) --rm npm_tests

    # start containers
    docker compose up
    docker compose up -d

    # setup database
    docker compose -f docker-compose-tasks-be.yml run --rm cli update_database.php

#### Static code analyzers (backend)

    docker compose -f docker-compose-tasks-be.yml run --rm psalm
    docker compose -f docker-compose-tasks-be.yml run --rm psalm_taint
    docker compose -f docker-compose-tasks-be.yml run --rm phpstan
    docker compose -f docker-compose-tasks-be.yml run --rm rector
    docker compose -f docker-compose-tasks-be.yml run --rm phpmd
    docker compose -f docker-compose-tasks-be.yml run --rm phpcsfixer

#### Static code analyzers and builders (frontend)

    docker compose -f docker-compose-tasks-fe.yml run --rm biome
    docker compose -f docker-compose-tasks-fe.yml run --rm stylelint
    docker compose -f docker-compose-tasks-fe.yml run --rm tsclint
    docker compose -f docker-compose-tasks-fe.yml run --rm htmlvalidate
    docker compose -f docker-compose-tasks-fe.yml run -u $(id -u) --rm esbuild

#### Tests (backend)

    docker compose -f docker-compose-tasks-be.yml run --rm phpunit
    docker compose -f docker-compose-tasks-be.yml run --rm phpunit_feature

#### Tests (frontend)

    docker compose -f docker-compose-tasks-fe.yml run -u $(id -u) --rm vitest
    docker compose -f docker-compose-tasks-e2e.yml run --rm playwright
    docker compose -f docker-compose-tasks-e2e.yml run -u $(id -u) --rm lighthouse

#### Security scanning

    docker images | grep example_tasks | awk '{print $1}' | xargs -n1 trivy image --scanners vuln
    trivy fs --scanners vuln,config,secret,license --license-full --skip-dirs "tasks_be/tests/vendor/" ./

    docker images | grep example_tasks | awk '{print $1}' | xargs -n1 docker scout cves --locations
    docker scout cves fs://.

#### Convert API blueprint to OpenAPI-JSON, OpenAPI-PHP and HTML

    chmod 0666 tasks_be/docs/api_openapi.json tasks_be/tests/data/api_openapi.php tasks_be/docs/api.html.gz
    docker compose -f docker-compose-tasks-be.yml run -u $(id -u) --rm apib2openapi
    docker compose -f docker-compose-tasks-be.yml run -u $(id -u) --rm apib2php
    docker compose -f docker-compose-tasks-be.yml run -u $(id -u) --rm apib2html

#### URLs

    http://127.0.0.1:8080/tasks/ (Frontend SPA)
    http://127.0.0.1:8080/v1/tasks (API endpoint)

    http://127.0.0.1:8080/docs/ (OpenAPI and HTML documentation)
    http://127.0.0.1:8080/coverage/ (code coverage)
    http://127.0.0.1:8025/ (Mailpit, catches all outgoing emails)

#### Command line tests

    export BASE=http://127.0.0.1:8080

    export TOKEN=$(curl -s -X POST -d '{"email":"foo@bar.baz","password":"insecure"}' "${BASE}/v1/customers/login" | jq -r '.token')
    echo $TOKEN

    curl -i -X POST -d '{"title":"test","duedate":"2020-05-22"}' -H "Authorization: ${TOKEN}" "${BASE}/v1/tasks"
    curl -i -X GET -H "Authorization: ${TOKEN}" "${BASE}/v1/tasks"
    curl -i -X PUT -d '{"title":"test","duedate":"2020-05-22","completed":true}' -H "Authorization: ${TOKEN}" \
        "${BASE}/v1/tasks/1"
    curl -i -X GET -H "Authorization: ${TOKEN}" "${BASE}/v1/tasks?completed=1"
    curl -i -X GET -H "Authorization: ${TOKEN}" "${BASE}/v1/tasks/1"
    curl -i -X DELETE -H "Authorization: ${TOKEN}" "${BASE}/v1/tasks/1"

    # import task queue into redis stream
    docker compose -f docker-compose-tasks-be.yml run --rm cli task_queue.php

    # import redis stream into clickhouse
    docker compose -f docker-compose-tasks-be.yml run --rm cli task_stream.php

    # generate bearer token for customer id "42" with email "foo.bar@example.com"
    docker compose -f docker-compose-tasks-be.yml run --rm cli generate_token.php \
        42 foo.bar@example.com

#### [architecture.md](https://github.com/thbley/php_frameworkless/blob/master/architecture.md)

#### [development.md](https://github.com/thbley/php_frameworkless/blob/master/development.md)
