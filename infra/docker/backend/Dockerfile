FROM php:8.3-cli-alpine

RUN apk add --no-cache bash git curl unzip libzip-dev icu-dev oniguruma-dev autoconf g++ make linux-headers
RUN docker-php-ext-install pdo pdo_mysql
RUN pecl install openswoole && docker-php-ext-enable openswoole

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY apps/backend/composer.json /app/composer.json
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist

COPY apps/backend /app

EXPOSE 8080 9502
CMD ["php", "-S", "0.0.0.0:8080", "-t", "public", "public/index.php"]
