# --- Stage 1: Build Frontend ---
FROM node:20-alpine AS frontend-builder
WORKDIR /app

# Copy and install frontend dependencies
COPY frontend/package*.json ./frontend/
RUN cd frontend && npm ci

# Copy frontend source and compile React assets into backend public directory
COPY frontend/ ./frontend/
COPY backend/ ./backend/
RUN cd frontend && npm run build

# --- Stage 2: Serve Laravel Backend ---
FROM php:8.2-apache
WORKDIR /var/www/html

# Install system dependencies & PostgreSQL dev libraries
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libpq-dev \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions required for Laravel and PostgreSQL
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip pdo pdo_pgsql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy backend codebase
COPY backend/ .

# Copy Frontend compiled build from Stage 1
COPY --from=frontend-builder /app/backend/public/app ./public/app

# Create bootstrap cache and storage directories
RUN mkdir -p bootstrap/cache storage/framework/sessions storage/framework/views storage/framework/cache storage/logs

# Create a minimal .env file so Laravel can boot
RUN touch .env

# Run Composer installation
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copy Apache configuration
COPY apache.conf /etc/apache2/sites-available/000-default.conf

# Create entrypoint script that caches config from ECS env vars at runtime
RUN echo '#!/bin/bash\n\
php artisan config:clear 2>/dev/null\n\
php artisan route:cache 2>/dev/null\n\
php artisan view:cache 2>/dev/null\n\
apache2-foreground' > /usr/local/bin/start.sh \
    && chmod +x /usr/local/bin/start.sh

# Set environment variables for production
ENV PORT=80
EXPOSE 80
CMD ["/usr/local/bin/start.sh"]
