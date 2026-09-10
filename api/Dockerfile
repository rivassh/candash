# ---- Base ----
FROM php:8.4-fpm-alpine AS base

# Create a non-root user for the application
RUN addgroup -S appgroup && adduser -S appuser -G appgroup

# Install required packages
RUN apk add --no-cache \
        git curl unzip libpq-dev icu-dev oniguruma-dev libzip-dev \
        autoconf g++ make linux-headers tzdata \
    && docker-php-ext-install pdo pdo_pgsql intl mbstring zip bcmath \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && cp /usr/share/zoneinfo/Asia/Tehran /etc/localtime \
    && echo "Asia/Tehran" > /etc/timezone

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

EXPOSE 8000

USER appuser

CMD ["sh", "-c", "php artisan config:clear && php artisan route:clear && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8000"]
