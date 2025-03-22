# Resmi PHP 8.2 ve Apache'li Laravel için Image
FROM php:8.2-apache

# Gerekli bağımlılıkları yükle
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    curl \
    && docker-php-ext-install pdo pdo_pgsql

# Composer'ı yükle
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Apache için Laravel gereksinimlerini ayarla
RUN a2enmod rewrite

# Çalışma dizinini ayarla
WORKDIR /var/www

# Laravel bağımlılıklarını yükle
COPY . /var/www
RUN composer install --no-dev --optimize-autoloader

# Laravel için gerekli izinleri ver
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Apache'yi başlat
CMD ["apache2-foreground"]
