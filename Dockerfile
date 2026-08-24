FROM php:8.4-cli-alpine

RUN apk add --no-cache postgresql-dev postgresql-client icu-dev libzip-dev oniguruma-dev nodejs npm \
    && docker-php-ext-install pdo_pgsql intl zip bcmath opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Dependências primeiro, para aproveitar o cache de camadas entre builds.
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-scripts --no-autoloader --prefer-dist

COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts

COPY . .
RUN composer dump-autoload --optimize && npm run build

# Fora de /app para o bind mount do compose não sobrescrever o script.
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8000
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
