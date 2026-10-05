FROM php:8.2-fpm

# Install system packages
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    zip \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev

# Configure GD
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_mysql \
    zip \
    gd

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy project
COPY . .

# Create Laravel framework directories
RUN mkdir -p storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    bootstrap/cache

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]