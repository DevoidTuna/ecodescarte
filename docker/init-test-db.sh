#!/bin/sh
# Executado uma única vez, na criação do volume do Postgres.
set -e
psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" <<-SQL
    CREATE DATABASE ${POSTGRES_DB}_test OWNER $POSTGRES_USER;
SQL
