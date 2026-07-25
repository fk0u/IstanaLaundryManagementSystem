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
RUN groupadd -g 1000 www \
    && useradd -u 1000 -ms /bin/sh -g www www

# Copy existing application directory contents
COPY . /var/www

# Copy existing application directory permissions
COPY --chown=www:www . /var/www

# Change current user to www
USER www

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
