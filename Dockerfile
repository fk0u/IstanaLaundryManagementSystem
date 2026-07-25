FROM php:8.3-fpm-alpine

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

# Add user for laravel application
RUN addgroup -g 1000 -S www \
    && adduser -u 1000 -S www -G www -s /bin/sh

# Copy existing application directory contents
COPY --chown=www:www . /var/www

# Install composer dependencies as www user
USER www
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Install npm dependencies and build assets
RUN npm ci && npm run build && rm -rf node_modules

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
