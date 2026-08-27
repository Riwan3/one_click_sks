# Production-Ready Dockerfile for OneClick Laravel 13 System
# Multi-stage build for optimized image size

FROM php:8.3-fpm-alpine AS builder

# Install system dependencies
RUN apk add --no-cache \
    curl \
    git \
    unzip \
    postgresql-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    gettext

# Install PHP extensions required for Laravel 13
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_pgsql \
    pdo_mysql \
    pgsql \
    bcmath \
    ctype \
    curl \
    dom \
    fileinfo \
    filter \
    gd \
    hash \
    json \
    mbstring \
    openssl \
    pcntl \
    pdo \
    reflection \
    session \
    simplexml \
    tokenizer \
    xml \
    xmlwriter

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy only composer files first (for better layer caching)
COPY composer.json composer.lock ./

# Install PHP dependencies (production only)
RUN composer install --no-interaction --no-dev --optimize-autoloader --no-scripts

# Copy application code
COPY . .

# Generate optimized autoloader
RUN composer dump-autoload --optimize

# Run Laravel installation commands (migrations will run at container startup)
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

# ===== Production Image =====
FROM php:8.3-fpm-alpine

# Install runtime dependencies only (smaller image)
RUN apk add --no-cache \
    postgresql-libs \
    mysql-client \
    curl \
    supervisor \
    gettext \
    libpng \
    libjpeg-turbo \
    freetype

# Install PHP extensions
RUN docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_pgsql \
    pdo_mysql \
    pgsql \
    bcmath \
    ctype \
    curl \
    dom \
    fileinfo \
    filter \
    gd \
    hash \
    json \
    mbstring \
    openssl \
    pcntl \
    pdo \
    reflection \
    session \
    simplexml \
    tokenizer \
    xml \
    xmlwriter

# Install Redis extension
RUN apk add --no-cache redis
RUN pecl install redis && docker-php-ext-enable redis

# Copy PHP configuration for production
COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.conf

# Create app user
RUN addgroup -g 1000 laravel && adduser -D -u 1000 -G laravel laravel

# Set working directory
WORKDIR /app

# Copy application and vendor from builder stage
COPY --from=builder --chown=laravel:laravel /app .

# Create necessary directories
RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views
RUN chown -R laravel:laravel storage bootstrap/cache

# Copy startup script
COPY docker/entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh

# Copy supervisor configuration for queue workers
COPY docker/supervisord.conf /etc/supervisord.conf

# Switch to laravel user
USER laravel

# Expose port
EXPOSE 9000

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=10s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

# Run entrypoint script
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

CMD ["php-fpm"]
