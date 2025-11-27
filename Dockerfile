FROM php:8.4

# Install system dependencies
RUN apt-get update -y && apt-get install -y \
    openssl \
    zip \
    unzip \
    git \
    libonig-dev \
    curl

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install PHP extensions
RUN docker-php-ext-install pdo mbstring
RUN pecl install redis \
    && docker-php-ext-enable redis


RUN apt-get update && apt-get install -y docker.io

# Uygulama çalışma dizini
WORKDIR /app

# Projeyi kopyala
COPY . /app

# Composer bağımlılıklarını yükle
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Laravel izinleri ve gerekli klasörler
RUN mkdir -p storage/framework/views storage/framework/cache storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Laravel için storage:link
RUN php artisan storage:link || true

# Uygulama komutu
CMD ["sh", "-c", "\
  php artisan config:clear && \
  php artisan config:cache && \
  php artisan event:clear && \
  php artisan event:cache && \
  php artisan route:clear && \
  php artisan route:cache && \
  php artisan view:clear && \
  php artisan migrate --force && \
  php artisan storage:link || true && \
  php artisan serve --host=0.0.0.0 --port=80" ]

# Port aç
EXPOSE 80
