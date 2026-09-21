# ---- Base ----
FROM php:8.4-fpm-bookworm AS base

# Install system deps (PHP extensions)
RUN apt-get update && apt-get install -y --no-install-recommends \
        git curl unzip libpq-dev libicu-dev libonig-dev libzip-dev \
        autoconf g++ make tzdata \
        xvfb x11vnc novnc websockify tcl tk chromium chromium-driver \
    && docker-php-ext-install pdo pdo_pgsql intl mbstring zip bcmath \
    && cp /usr/share/zoneinfo/Asia/Tehran /etc/localtime \
    && echo "Asia/Tehran" > /etc/timezone

# Install Node.js for browser agent
RUN curl -fsSL https://deb.nodesource.com/setup_22.x -o /tmp/nodesource.sh && \
    bash /tmp/nodesource.sh && apt-get install -y nodejs

# Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# ---- Browser agent deps ----
COPY browser-agent/package.json browser-agent/package-lock.json* ./browser-agent/
RUN cd browser-agent && npm install --production

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
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000 9999 6080

HEALTHCHECK --interval=10s --timeout=5s --start-period=20s --retries=5 \
    CMD curl -fsS --max-time 3 http://127.0.0.1:8000/api/health >/dev/null || exit 1

CMD ["sh", "-c", "php artisan config:clear && php artisan route:clear && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8000 & node browser-agent/server.js"]
