set -eu

SOURCE_DB="${SOURCE_DB:-criancafeliz_test}"
RESTORE_DB="${RESTORE_DB:-criancafeliz_restore_test}"
DUMP_FILE="${DUMP_FILE:-/tmp/criancafeliz_backup_restore_test.sql}"
MYSQL_ROOT_PASSWORD="${MYSQL_ROOT_PASSWORD:-root}"

mysql_root() {
  mysql -uroot -p"${MYSQL_ROOT_PASSWORD}" "$@"
}

mysqldump_root() {
  mysqldump -uroot -p"${MYSQL_ROOT_PASSWORD}" "$@"
}

cleanup() {
  rm -f "${DUMP_FILE}"
  mysql_root -e "DROP DATABASE IF EXISTS \`${RESTORE_DB}\`;" >/dev/null 2>&1 || true
}

trap cleanup EXIT
cleanup

mysqldump_root --single-transaction --routines --triggers "${SOURCE_DB}" > "${DUMP_FILE}"
test -s "${DUMP_FILE}"

mysql_root -e "CREATE DATABASE \`${RESTORE_DB}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql_root "${RESTORE_DB}" < "${DUMP_FILE}"

tables="$(mysql_root -N -B -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='${RESTORE_DB}';")"
admin_count="$(mysql_root -N -B "${RESTORE_DB}" -e "SELECT COUNT(*) FROM Usuario WHERE email = 'admin@criancafeliz.org';")"
atendidos_count="$(mysql_root -N -B "${RESTORE_DB}" -e "SELECT COUNT(*) FROM Atendido;")"

test "${tables:-0}" -ge 10
test "${admin_count:-0}" -ge 1

echo "OK: backup restaurado em ${RESTORE_DB} com ${tables} tabelas e ${atendidos_count} atendidos."
