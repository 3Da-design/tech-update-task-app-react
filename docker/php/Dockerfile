FROM php:8.4-fpm-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip libpq-dev libzip-dev libicu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j"$(nproc)" intl pdo_pgsql opcache pcntl bcmath zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# php-fpm はワーカー起動時に環境変数を消すため、Compose の DB_HOST 等が届かない。
# Laravel が .env の 127.0.0.1 だけを見てしまいコンテナ内から DB に繋がらない問題を防ぐ。
COPY docker/php/zz-clear-env.conf /usr/local/etc/php-fpm.d/zz-clear-env.conf

COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-app-entrypoint
RUN chmod +x /usr/local/bin/docker-app-entrypoint

ENTRYPOINT ["docker-app-entrypoint"]
CMD ["php-fpm"]
