#!/bin/bash
set -e

echo "Importing Crianca Feliz schema..."

mysql -uroot -p"${MYSQL_ROOT_PASSWORD}" "${MYSQL_DATABASE}" \
    < /docker-init/SETUP_COMPLETO_FINAL.sql
