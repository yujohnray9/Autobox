
FROM dunglas/frankenphp:php8.2-trixie

WORKDIR /app

# Install PHP extensions required by AUTOBOX
RUN install-php-extensions \
    gd \
    pdo_mysql \
    redis \
    zip \
    bcmath \
    pcntl \
    opcache

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy Laravel project
COPY . .

# Install PHP dependencies
RUN composer install \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --no-interaction \
    --no-progress

# Build frontend assets
FROM node:22-bookworm-slim AS frontend

WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY . .
RUN npm run build

# Final application image
FROM dunglas/frankenphp:php8.2-trixie

WORKDIR /app

RUN install-php-extensions \
    gd \
    pdo_mysql \
    redis \
    zip \
    bcmath \
    pcntl \
    opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY --from=0 /app /app
COPY --from=frontend /app/public/build /app/public/build

RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

ENV APP_ENV=production
ENV APP_DEBUG=false
ENV SERVER_NAME=:8080

EXPOSE 8080

CMD ["frankenphp", "php-server", "--root", "public"]
