#!/bin/sh
# Prepara a aplicação a cada boot do container: .env, chave, migrations e seed.
# Roda no start e não no build porque nenhuma dessas etapas é possível durante
# o `docker build` — não há .env e o banco ainda não existe.
set -e

cd /app

# .env é gitignored, então na primeira subida o container cria o seu.
if [ ! -f .env ]; then
    echo "==> criando .env a partir de .env.example"
    cp .env.example .env
fi

# Grava uma chave no .env, criando a linha se ela não existir.
set_env() {
    if grep -q "^$1=" .env; then
        sed -i "s|^$1=.*|$1=$2|" .env
    else
        printf '%s=%s\n' "$1" "$2" >> .env
    fi
}

# As credenciais do banco precisam estar no .env, e não apenas no ambiente do
# processo: `artisan serve` repassa ao servidor filho somente uma lista fixa de
# variáveis (APP_ENV, PATH, LARAVEL_SAIL e as do Xdebug). DB_HOST e as demais
# são descartadas, então o app servido leria o .env e tentaria 127.0.0.1 —
# mesmo com as migrations tendo funcionado, que rodam no processo pai.
echo "==> aplicando as credenciais do compose ao .env"
set_env DB_CONNECTION "${DB_CONNECTION:-pgsql}"
set_env DB_HOST "${DB_HOST:-db}"
set_env DB_PORT "${DB_PORT:-5432}"
set_env DB_DATABASE "${DB_DATABASE:-ecodescarte}"
set_env DB_USERNAME "${DB_USERNAME:-ecodescarte}"
set_env DB_PASSWORD "${DB_PASSWORD:-secret}"

# Gera a APP_KEY apenas se ainda não houver uma.
if ! grep -q '^APP_KEY=base64:' .env; then
    echo "==> gerando APP_KEY"
    php artisan key:generate --force
fi

# O healthcheck do compose já espera o Postgres; esta espera cobre o caso de
# o container ser iniciado fora do compose.
until pg_isready -h "${DB_HOST:-db}" -p "${DB_PORT:-5432}" -U "${DB_USERNAME:-ecodescarte}" >/dev/null 2>&1; do
    echo "==> aguardando o PostgreSQL em ${DB_HOST:-db}:${DB_PORT:-5432}"
    sleep 2
done

echo "==> migrations"
php artisan migrate --force

# O seeder usa firstOrCreate, então repetir a cada boot é seguro.
echo "==> seed"
php artisan db:seed --force

exec "$@"
