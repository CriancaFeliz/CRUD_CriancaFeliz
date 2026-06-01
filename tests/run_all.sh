#!/usr/bin/env bash
set -euo pipefail

KEEP_CONTAINERS="${KEEP_CONTAINERS:-0}"
COMPOSE=(docker compose -f docker-compose.test.yml)

cleanup() {
  if [[ "$KEEP_CONTAINERS" != "1" ]]; then
    "${COMPOSE[@]}" down -v --remove-orphans
  else
    echo
    echo "Containers mantidos para inspecao: docker compose -f docker-compose.test.yml ps"
  fi
}

trap cleanup EXIT

echo "==> PHP unitarios locais"
php tests/run.php

echo "==> Lint PHP local"
find . \
  -path './database/legacy_dumps' -prune -o \
  -path './vendor' -prune -o \
  -name '*.php' -print0 | xargs -0 -n1 php -l

echo "==> Config Docker de teste"
"${COMPOSE[@]}" config --quiet

echo "==> Recriar ambiente Docker de teste"
"${COMPOSE[@]}" down -v --remove-orphans
"${COMPOSE[@]}" up -d --build

echo "==> Aguardar aplicacao HTTP"
for i in {1..60}; do
  if curl -fsS http://localhost:8090/ >/dev/null; then
    break
  fi

  if [[ "$i" == "60" ]]; then
    echo "Aplicacao nao respondeu em http://localhost:8090/" >&2
    exit 1
  fi

  sleep 2
done

echo "==> Testes de integracao com banco"
"${COMPOSE[@]}" exec -T app php tests/wait_for_database.php
"${COMPOSE[@]}" exec -T app php tests/run_integration.php

echo "==> Smoke tests HTTP"
"${COMPOSE[@]}" exec -T app php tests/run_http_smoke.php

echo "==> Backup e restauracao do banco"
"${COMPOSE[@]}" exec -T db sh -lc "tr -d '\r' < /backup_restore_check.sh | sh"
