FROM php:8.4-fpm

# Arguments defined in docker-compose.yml
ARG user=dharanikumar
ARG uid=1000

# Install system dependencies
RUN apt-get update && apt-get install -y \
    bash \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN useradd -G sudo,www-data -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Set working directory
WORKDIR /var/www

USER $user

EXPOSE 8000

# Run from dir that contains artisan (works whether /var/www is backend or repo root with gym-tracker-backend inside)
CMD ["sh", "-c", "if [ -f /var/www/artisan ]; then cd /var/www; else cd /var/www/gym-tracker-backend; fi && exec php artisan serve --host=0.0.0.0 --port=8000"]
