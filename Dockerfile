FROM dunglas/frankenphp:latest-php8.3-alpine

RUN apk add --no-cache \
    postgresql-dev \
    linux-headers \
    $PHPIZE_DEPS

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

ARG INSTALL_XDEBUG=false
RUN if [ "$INSTALL_XDEBUG" = "true" ]; then \
    pecl install xdebug && docker-php-ext-enable xdebug; \
    fi

# Install PHP extensions
RUN pecl install redis && docker-php-ext-enable redis

RUN docker-php-ext-install \
    pdo_pgsql

COPY Caddyfile /etc/caddy/Caddyfile
ENV APP_ENV=prod
COPY . /app
RUN composer install --no-dev --optimize-autoloader
RUN php bin/console asset-map:compile

CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
