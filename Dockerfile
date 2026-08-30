FROM php:8.4-cli

# System dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    && docker-php-ext-install \
        pdo_pgsql \
        pgsql \
        bcmath \
        intl \
        zip \
    && rm -rf /var/lib/apt/lists/*

# Install Redis extension
# RUN pecl install redis \
#     && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

# Install PHP dependencies
RUN composer install

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

# CMD ["php-fpm"]