# syntax=docker/dockerfile:1

# =============================================================================
# Build stage: install PHP + Node dependencies dan kompilasi asset frontend.
# Node dibutuhkan karena plugin @laravel/vite-plugin-wayfinder menjalankan
# `php artisan wayfinder:generate` saat proses build.
# =============================================================================
FROM php:8.3-cli-bookworm AS build

ENV DEBIAN_FRONTEND=noninteractive

# Nilai VITE_* di-bake saat build, jadi harus dikirim sebagai build arg.
ARG VITE_APP_NAME=ReValue
ARG VITE_BASEMAP_API_KEY=
ENV VITE_APP_NAME=${VITE_APP_NAME} \
    VITE_BASEMAP_API_KEY=${VITE_BASEMAP_API_KEY}

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        git \
        gnupg \
        unzip \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --prefer-dist \
        --no-interaction \
        --no-progress

COPY package.json package-lock.json .npmrc ./
RUN npm ci --no-audit --no-fund

COPY . .

# Cache dev (pint/phpstan) tidak boleh ikut ke image.
RUN rm -f bootstrap/cache/*.php \
    && composer dump-autoload --no-dev --optimize \
    && npm run build

# =============================================================================
# Export stage: buang artefak yang hanya dibutuhkan saat build.
# =============================================================================
FROM build AS export

RUN rm -rf node_modules .git .github tests

# =============================================================================
# Runtime stage: nginx + php-fpm dijalankan oleh supervisord.
# =============================================================================
FROM php:8.3-fpm-alpine AS runtime

RUN apk add --no-cache \
        curl \
        nginx \
        supervisor \
        tzdata \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql opcache pcntl \
    && apk del .build-deps \
    && rm -f /usr/local/etc/php-fpm.d/zz-docker.conf

WORKDIR /var/www/html

COPY --from=export --chown=www-data:www-data /app ./

COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/php/zz-app.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8005

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD curl -fsS http://127.0.0.1:8005/up || exit 1

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
