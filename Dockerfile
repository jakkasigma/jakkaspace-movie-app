FROM php:8.3-fpm-bullseye

# Install system dependencies + PHP extensions in one layer
RUN apt-get update && apt-get install -y \
    git curl zip unzip \
    libpng-dev libonig-dev libxml2-dev libzip-dev \
    libfreetype6-dev libjpeg62-turbo-dev \
    libmariadb-dev-compat libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo pdo_mysql mbstring exif pcntl bcmath gd zip intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Node.js 20 via NodeSource
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy and install PHP deps (cached layer)
COPY composer.json composer.lock ./
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install \
    --no-dev --no-interaction --prefer-dist \
    --optimize-autoloader --no-scripts

# Copy and install Node deps (cached layer)
COPY package.json package-lock.json ./
RUN npm ci --prefer-offline

# Copy full app
COPY . .

# Finish composer setup + build frontend
RUN COMPOSER_ALLOW_SUPERUSER=1 composer dump-autoload --optimize \
    && npm run build \
    && php artisan storage:link --force \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8080

CMD ["sh", "-c", "\
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    php artisan migrate --force && \
    php artisan db:seed --class=ProductionSeeder --force && \
    php artisan serve --host=0.0.0.0 --port=${PORT:-8080} \
"]
