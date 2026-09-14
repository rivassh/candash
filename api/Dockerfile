# ---- Base ----
FROM php:8.4-fpm-alpine AS base

# Install system deps (PHP extensions)
RUN addgroup -S appgroup && adduser -S appuser -G appgroup && \
    apk add --no-cache \
        git curl unzip libpq-dev icu-dev oniguruma-dev libzip-dev \
        autoconf g++ make linux-headers tzdata \
    && docker-php-ext-install pdo pdo_pgsql intl mbstring zip bcmath \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && cp /usr/share/zoneinfo/Asia/Tehran /etc/localtime \
    && echo "Asia/Tehran" > /etc/timezone

# Install Node.js for browser agent (separate RUN to avoid Alpine cache conflicts)
RUN apk add --no-cache nodejs npm 2>/dev/null || apk add --no-cache --repository=http://dl-cdn.alpinelinux.org/alpine/edge/community nodejs npm 2>/dev/null || true

# Install VNC stack for browser automation
RUN apk add --no-cache xvfb x11vnc novnc websockify tcl tk 2>/dev/null || true

# Install Playwright browsers
RUN npm install -g playwright && \
    npx playwright install chromium --with-deps 2>/dev/null || true

# Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# ---- Composer dependencies ----
FROM base AS vendor

COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction || \
    composer install --no-dev --no-scripts --prefer-dist --no-interaction

# ---- Runtime ----
FROM vendor AS runtime

COPY . .

RUN composer dump-autoload --optimize --no-interaction \
    && mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache \
    && chown -R appgroup:appuser storage bootstrap/cache

EXPOSE 8000 9999 6080

USER appuser

CMD ["sh", "-c", "php artisan config:clear && php artisan route:clear && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8000 & node browser-agent/server.js"]