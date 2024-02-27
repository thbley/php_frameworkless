#!/bin/bash

set -euxo pipefail

trivy -v
w3m -version
docker version
docker compose version

docker compose -f docker-compose-tasks-be.yml -f docker-compose-tasks-fe.yml build --parallel --quiet

trivy fs --no-progress --scanners vuln,config,secret ./
docker images | grep example_tasks | awk '{print $1}' | xargs -n1 trivy image --scanners vuln

trivy fs --scanners license --license-full --skip-dirs "tasks_be/tests/vendor/" ./
trivy image --scanners license example_tasks_php

docker scout cves -e --locations fs://.
docker images | grep example_tasks | awk '{print $1}' | xargs -n1 docker scout cves -e --locations

docker compose up -d mysql clickhouse redis

docker compose -f docker-compose-tasks-be.yml run --rm cli -i | grep -Ei "version|information"

docker compose -f docker-compose-tasks-be.yml run --rm composer validate --no-cache
docker compose -f docker-compose-tasks-be.yml run --rm composer check-platform-reqs --no-cache
docker compose -f docker-compose-tasks-be.yml run -u $(id -u) --rm composer
docker compose -f docker-compose-tasks-be.yml run --rm composer outdated --strict --no-cache
docker compose -f docker-compose-tasks-be.yml run --rm composer audit

docker compose -f docker-compose-tasks-be.yml run --rm composer_tests validate --no-cache
docker compose -f docker-compose-tasks-be.yml run --rm composer_tests check-platform-reqs --no-cache
docker compose -f docker-compose-tasks-be.yml run -u $(id -u) --rm composer_tests
docker compose -f docker-compose-tasks-be.yml run --rm composer_tests outdated --no-cache
docker compose -f docker-compose-tasks-be.yml run --rm composer_tests audit

sh -c '! grep --include="*.php" --exclude-dir=vendor -rn ".\{121\}" tasks'
docker compose -f docker-compose-tasks-be.yml run --rm phpcsfixer
docker compose -f docker-compose-tasks-be.yml run --rm psalm
docker compose -f docker-compose-tasks-be.yml run --rm psalm_taint
docker compose -f docker-compose-tasks-be.yml run --rm phpstan
docker compose -f docker-compose-tasks-be.yml run --rm rector
docker compose -f docker-compose-tasks-be.yml run --rm phpmd

until docker compose exec -T mysql mysql -uroot -proot -e "select now()"; do echo "waiting..."; sleep 1; done

docker compose -f docker-compose-tasks-be.yml run --rm cli update_database.php

docker compose -f docker-compose-tasks-be.yml run --rm phpunit
docker compose -f docker-compose-tasks-be.yml run --rm phpunit_feature

docker compose -f docker-compose-tasks-fe.yml run -u $(id -u) --rm npm
docker compose -f docker-compose-tasks-fe.yml run --rm npm outdated
docker compose -f docker-compose-tasks-fe.yml run --rm npm audit

docker compose -f docker-compose-tasks-fe.yml run -u $(id -u) --rm npm_tests
docker compose -f docker-compose-tasks-fe.yml run --rm npm_tests outdated
docker compose -f docker-compose-tasks-fe.yml run --rm npm_tests audit

docker compose -f docker-compose-tasks-fe.yml run --rm biome
docker compose -f docker-compose-tasks-fe.yml run --rm stylelint
docker compose -f docker-compose-tasks-fe.yml run --rm tsclint
docker compose -f docker-compose-tasks-fe.yml run --rm htmlvalidate
docker compose -f docker-compose-tasks-fe.yml run -u $(id -u) --rm esbuild

docker compose -f docker-compose-tasks-fe.yml run -u $(id -u) --rm vitest
docker compose -f docker-compose-tasks-e2e.yml run --rm playwright
docker compose -f docker-compose-tasks-e2e.yml run -u $(id -u) --rm lighthouse

docker stats --no-stream

docker compose exec nginx cat /var/www/coverage/tasks/index.html | sed 's/<img[^>]*>//' | w3m -T text/html -dump -cols 160
