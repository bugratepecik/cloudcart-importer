# Resmi PHP 8.2 Image'ını kullan
FROM php:8.2-cli

# Gerekli bağımlılıkları yükle
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    curl \
    nodejs \
    npm \
    fish \
    && docker-php-ext-install pdo pdo_pgsql

# Composer'ı yükle
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Çalışma dizinini ayarla
WORKDIR /var/www

# Laravel dosyalarını kopyala ve bağımlılıkları yükle
COPY . /var/www
RUN composer install --no-dev --optimize-autoloader

# Laravel için gerekli izinleri ver
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Docker Container başladığında Laravel Server'ı çalıştır
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
