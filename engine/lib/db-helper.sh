#!/usr/bin/env bash
# Funciones auxiliares para MariaDB / MySQL en PiruluGCP

# shellcheck disable=SC1091
source "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/common.sh"

run_sql() {
    local query="$1"
    if [ -f "/root/.my.cnf" ]; then
        mysql --defaults-file="/root/.my.cnf" -e "$query"
    else
        mysql -u root -e "$query"
    fi
}

db_exists() {
    local db_name="$1"
    local count
    count=$(run_sql "SELECT COUNT(*) FROM information_schema.schemata WHERE schema_name = '${db_name}';" | tail -n 1)
    [ "$count" -gt 0 ]
}

db_user_exists() {
    local db_user="$1"
    local count
    count=$(run_sql "SELECT COUNT(*) FROM mysql.user WHERE user = '${db_user}';" | tail -n 1)
    [ "$count" -gt 0 ]
}
