FROM richarvey/nginx-php-fpm:latest

# Install dependencies sistem untuk PostgreSQL (diperlukan untuk pdo_pgsql)
RUN apk update && apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl

# Install extension pdo_pgsql yang dibutuhkan API FOMO kamu
RUN docker-php-ext-install pdo_pgsql

# Atur lokasi project di dalam container sesuai standar image ini
COPY . /var/www/html

# Set working directory
WORKDIR /var/www/html

# Environment variables bawaan image ini untuk mengarahkan Nginx ke folder public Laravel
ENV WEBROOT /var/www/html/public
ENV APP_ENV production

# Jalankan composer install untuk mengunduh package Laravel
RUN composer install --no-dev --allow-plugins --optimize-autoloader

# Atur permission agar Laravel bisa menulis logs dan cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Render akan otomatis membaca port ini
EXPOSE 80