FROM php:8.3-fpm

ARG user=caiopadilha
ARG uid=1000

# Install dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    curl \
    git \
    libonig-dev \
    libpng-dev \
    libxml2-dev \
    unzip \
    zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install mbstring bcmath

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u "$uid" -d "/home/$user" "$user" \
    && mkdir -p "/home/$user/.composer" \
    && chown -R "$user:$user" "/home/$user"

# Set working directory
WORKDIR /var/www

USER $user