# Stage 1: Build frontend assets
FROM node:24-alpine AS frontend

WORKDIR /app
COPY package*.json ./
RUN npm ci --no-audit --prefer-offline
COPY vite.config.js ./
COPY resources/ resources/
COPY public/ public/
RUN npm run build || echo "Vite build skipped (no frontend assets)"

# Stage 2: Install PHP dependencies
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --prefer-dist --optimize-autoloader

# Stage 3: Production runtime
FROM ubuntu:24.04

LABEL maintainer="SPK Desa - Komisi Informasi Bali"

ARG WWWGROUP=1000
ARG WWWUSER=1000

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=Asia/Makassar
ENV LANG=C.UTF-8

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Install PHP 8.5 + Nginx + extensions
RUN apt-get update && apt-get upgrade -y \
    && apt-get install -y --no-install-recommends \
        ca-certificates curl gnupg zip unzip nginx supervisor \
    && curl -sS 'https://keyserver.ubuntu.com/pks/lookup?op=get&search=0xb8dc7e53946656efbce4c1dd71daeaab4ad4cab6' | gpg --dearmor | tee /etc/apt/keyrings/ppa_ondrej_php.gpg > /dev/null \
    && echo "deb [signed-by=/etc/apt/keyrings/ppa_ondrej_php.gpg] https://ppa.launchpadcontent.net/ondrej/php/ubuntu noble main" > /etc/apt/sources.list.d/ppa_ondrej_php.list \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        php8.5-fpm \
        php8.5-mysql \
        php8.5-pgsql \
        php8.5-sqlite3 \
        php8.5-gd \
        php8.5-curl \
        php8.5-mbstring \
        php8.5-xml \
        php8.5-zip \
        php8.5-bcmath \
        php8.5-intl \
        php8.5-redis \
        php8.5-imagick \
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Configure PHP-FPM
RUN sed -i 's/^user =.*/user = www-data/' /etc/php/8.5/fpm/pool.d/www.conf \
    && sed -i 's/^group =.*/group = www-data/' /etc/php/8.5/fpm/pool.d/www.conf \
    && sed -i 's/^listen =.*/listen = 127.0.0.1:9000/' /etc/php/8.5/fpm/pool.d/www.conf \
    && sed -i 's/^;clear_env =.*/clear_env = no/' /etc/php/8.5/fpm/pool.d/www.conf

# PHP ini
RUN echo "upload_max_filesize = 20M" > /etc/php/8.5/fpm/conf.d/99-custom.ini \
    && echo "post_max_size = 20M" >> /etc/php/8.5/fpm/conf.d/99-custom.ini \
    && echo "max_execution_time = 60" >> /etc/php/8.5/fpm/conf.d/99-custom.ini \
    && echo "memory_limit = 256M" >> /etc/php/8.5/fpm/conf.d/99-custom.ini

# Nginx config
RUN rm /etc/nginx/sites-enabled/default
COPY docker/nginx.conf /etc/nginx/sites-enabled/default

# Supervisor config for PHP-FPM + Nginx
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy application
COPY --chown=www-data:www-data . /var/www/html
COPY --from=vendor --chown=www-data:www-data /app/vendor /var/www/html/vendor
COPY --from=frontend --chown=www-data:www-data /app/public/build /var/www/html/public/build

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
