# syntax=docker/dockerfile:1

# ---------- Stage 1: Frontend assets ----------
FROM node:20-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm ci --no-audit --no-fund || npm install --no-audit --no-fund

COPY vite.config.js postcss.config.js tailwind.config.js ./
COPY resources ./resources
RUN npm run build


# ---------- Stage 2: Composer dependencies ----------
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock* ./

RUN composer install \
        --no-dev \
        --no-scripts \
        --no-interaction \
        --prefer-dist \
        --optimize-autoloader


# ---------- Stage 3: PHP base with extensions ----------
FROM php:8.3-fpm-alpine AS base

RUN apk add --no-cache \
        nginx \
        supervisor \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        oniguruma-dev \
        icu-dev \
        linux-headers \
        $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_mysql \
        zip \
    && apk del $PHPIZE_DEPS \
    && rm -rf /var/cache/apk/*

WORKDIR /var/www/html

COPY docker/php.ini /usr/local/etc/php/conf.d/99-app.ini
COPY docker/nginx.conf.template /etc/nginx/templates/default.conf.template
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build
COPY . .

RUN mkdir -p storage/framework/cache/data \
             storage/framework/sessions \
             storage/framework/views \
             storage/logs \
             storage/app/public \
             bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache


# ---------- Target: generic Docker host ----------
FROM base AS app

ENV PORT=8000 \
    RUN_QUEUE=false \
    RUN_SCHEDULER=false

EXPOSE 8000

ENTRYPOINT ["/usr/local/bin/entrypoint"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]


# ---------- Target: Vercel container runtime ----------
FROM base AS vercel

ENV PORT=8080 \
    APP_ENV=production \
    APP_DEBUG=false \
    VIEW_COMPILED_PATH=/tmp/views \
    CACHE_STORE=file \
    CACHE_PREFIX=nets \
    SESSION_DRIVER=database \
    QUEUE_CONNECTION=sync \
    LOG_CHANNEL=stderr \
    FILESYSTEM_DISK=s3 \
    RUN_QUEUE=false \
    RUN_SCHEDULER=false

EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/entrypoint"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]