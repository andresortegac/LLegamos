FROM node:22-alpine AS assets
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY resources ./resources
COPY vite.config.js ./
RUN npm run build

FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts
COPY . .
RUN composer dump-autoload --no-dev --classmap-authoritative --no-interaction

FROM php:8.3-fpm-alpine
RUN apk add --no-cache icu-libs libpng libjpeg-turbo libwebp libzip postgresql-libs supervisor \
 && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS icu-dev libpng-dev libjpeg-turbo-dev libwebp-dev libzip-dev postgresql-dev linux-headers \
 && docker-php-ext-configure gd --with-jpeg --with-webp \
 && docker-php-ext-install -j$(nproc) bcmath gd intl opcache pcntl pdo_pgsql zip \
 && pecl install redis && docker-php-ext-enable redis \
 && apk del .build-deps
WORKDIR /var/www/html
COPY --from=vendor /app /var/www/html
COPY --from=assets /app/public/build /var/www/html/public/build
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-llegamos.ini
RUN chown -R www-data:www-data storage bootstrap/cache && chmod -R ug+rwX storage bootstrap/cache
USER www-data
CMD ["php-fpm","-F"]