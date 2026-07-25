FROM php:8.4-fpm-alpine

# Set working directory
WORKDIR /var/www

# Install system dependencies
RUN apk update && apk add --no-cache \
    build-base \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    icu-dev \
    oniguruma-dev \
    nodejs \
    npm \
    netcat-openbsd \
    shadow

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        zip \
        gd \
        bcmath \
        exif \
        pcntl \
        intl \
        opcache

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . /var/www

# Ensure bootstrap/cache exists (excluded by .dockerignore)
RUN mkdir -p /var/www/bootstrap/cache

# Install composer dependencies AS ROOT (vendor dir needs root to create)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Install npm dependencies and build assets
RUN npm ci && npm run build && rm -rf node_modules

# Add non-root user for laravel application
RUN addgroup -g 1000 -S www \
    && adduser -u 1000 -S www -G www -s /bin/sh

# Fix ownership AFTER install
RUN chown -R www:www /var/www

# Switch to non-root user
USER www

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
