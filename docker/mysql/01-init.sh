#!/bin/bash
set -e

echo "Importing Crianca Feliz schema..."

sed 's/CHARACTER_SET_COLLATION_CONNECTION/COLLATION_CONNECTION/g' \
    /docker-init/SETUP_COMPLETO_FINAL.sql \
    | mysql -uroot -p"${MYSQL_ROOT_PASSWORD}" "${MYSQL_DATABASE}"
