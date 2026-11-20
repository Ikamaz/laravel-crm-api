FROM php:8.4-cli

ARG APP_UID=1000
ARG APP_GID=1000

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libicu-dev libonig-dev libzip-dev \
    && docker-php-ext-install -j"$(nproc)" bcmath intl mbstring opcache pcntl pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/* \
    && mkdir -p /var/www/html/vendor /var/www/html/storage /var/www/html/bootstrap/cache \
    && chown -R "${APP_UID}:${APP_GID}" /var/www/html

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

ENV COMPOSER_HOME=/tmp/composer
