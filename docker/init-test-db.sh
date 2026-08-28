#!/bin/sh
# Runs exactly once, when the PostgreSQL volume is created.
set -e
psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" <<-SQL
    CREATE DATABASE ${POSTGRES_DB}_test OWNER $POSTGRES_USER;
SQL
