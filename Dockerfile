# STAGE 1: PHP Dependencies (Composer)
FROM composer:2.7 AS vendor
RUN sed -i 's/dl-cdn.alpinelinux.org/uk.alpinelinux.org/g' /etc/apk/repositories || true
WORKDIR /app
COPY composer.json composer.lock* ./
COPY database/ database/
RUN for i in 1 2 3; do \
        composer install \
            --no-dev \
            --no-interaction \
            --prefer-dist \
            --optimize-autoloader \
            --ignore-platform-reqs \
            --no-scripts \
        && break || { echo "composer attempt $i failed, retrying..."; sleep 15; }; \
    done

# STAGE 2: Frontend Assets (Node)
FROM node:22-alpine AS assets
WORKDIR /app
COPY package*.json ./
RUN for i in 1 2 3; do \
        npm ci \
        && break || { echo "npm attempt $i failed, retrying..."; sleep 15; }; \
    done
COPY . .
RUN npm run build

# STAGE 3: Extension Builder (Heavy Compilation)
FROM php:8.3.1-fpm-alpine3.19 AS builder
RUN sed -i 's/dl-cdn.alpinelinux.org/uk.alpinelinux.org/g' /etc/apk/repositories || true
RUN for i in 1 2 3; do \
        apk add --no-cache \
            icu-dev \
            libzip-dev \
            zip \
            unzip \
            autoconf \
            g++ \
            gcc \
            libc-dev \
            make \
        && break || { echo "apk attempt $i failed, retrying..."; sleep 15; }; \
    done \
    && docker-php-ext-install -j$(nproc) pdo_mysql opcache zip pcntl intl \
    && pecl install redis \
    && docker-php-ext-enable redis

# STAGE 4: Final Production Runtime
FROM php:8.3.1-fpm-alpine3.19
WORKDIR /var/www/html

# Only install runtime dependencies (no build tools)
RUN for i in 1 2 3; do \
        apk add --no-cache \
            icu-libs \
            libzip \
            zip \
            unzip \
        && break || { echo "apk attempt $i failed, retrying..."; sleep 15; }; \
    done \
    && sed -i 's/listen = 127.0.0.1:9000/listen = 9000/g' /usr/local/etc/php-fpm.d/www.conf
    
# Copy compiled extensions from builder stage
COPY --from=builder /usr/local/lib/php/extensions /usr/local/lib/php/extensions
COPY --from=builder /usr/local/etc/php/conf.d /usr/local/etc/php/conf.d

# High Performance OPcache & JIT Compiler settings
RUN echo -e "opcache.enable=1\n\
opcache.enable_cli=1\n\
opcache.jit_buffer_size=100M\n\
opcache.jit=tracing\n\
opcache.memory_consumption=256\n\
opcache.max_accelerated_files=20000\n\
opcache.validate_timestamps=0\n\
opcache.interned_strings_buffer=16\n\
opcache.fast_shutdown=1" > /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

# Copy application files with correct ownership
COPY --chown=www-data:www-data . .
COPY --from=vendor --chown=www-data:www-data /app/vendor/ vendor/
COPY --from=assets --chown=www-data:www-data /app/public/build public/build

# Setup the entrypoint sequence
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# User Permissions switch safely
USER www-data
EXPOSE 9000
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
